<?php

declare(strict_types=1);
namespace App\Repository\Member;

use PDO;
use PDOException;
use InvalidArgumentException;
use DateTimeImmutable;
use Throwable;

/**
 * Akses data member: tabel members, member_interests, member_links, member_gallery.
 *
 * CoreMember   : create, read, delete + query untuk halaman publik (direktori & profil).
 * UpdateMember : ubah profil, ganti interest/link, kelola gallery.
 *
 * Semua query memakai prepared statement, kolom yang boleh diubah dibatasi whitelist,
 * dan input yang tidak valid melempar InvalidArgumentException.
 */
class CoreMember
{
    /** Kolom tabel `members` yang boleh diisi dari luar. */
    private const FIELDS = ['name', 'username', 'bio', 'about', 'avatar', 'cover', 'location', 'joined_at'];

    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /* ---------- Create / Read / Delete ---------- */

    /**
     * Tambah member baru. Wajib: name, username.
     * Opsional: bio, about, avatar, cover, location, joined_at (Y-m-d).
     *
     * @return int|false id member baru, atau false jika username sudah dipakai.
     */
    public function insert_member(array $data): int|false
    {
        $clean = $this->clean_member_data($data, true);
        $columns = array_keys($clean);

        $sql = sprintf(
            'INSERT INTO members (%s) VALUES (%s)',
            implode(', ', $columns),
            implode(', ', array_map(static fn(string $c): string => ':' . $c, $columns))
        );

        try {
            $this->db->prepare($sql)->execute($clean);
        } catch (PDOException $e) {
            if ($this->is_duplicate($e)) {
                return false;
            }
            throw $e;
        }

        return (int) $this->db->lastInsertId();
    }

    /** Baris mentah dari tabel members (tanpa interest/link/gallery), atau null. */
    public function read_member(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM members WHERE username = :username');
        $stmt->execute(['username' => strtolower(trim($username))]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Hapus berdasarkan id (diutamakan) atau username. Interest/link/gallery ikut terhapus (CASCADE). */
    public function delete_member(?string $username = null, ?int $id = null): bool
    {
        if ($id !== null) {
            $stmt = $this->db->prepare('DELETE FROM members WHERE id = :id');
            $stmt->execute(['id' => $id]);
        } elseif ($username !== null) {
            $stmt = $this->db->prepare('DELETE FROM members WHERE username = :username');
            $stmt->execute(['username' => strtolower(trim($username))]);
        } else {
            throw new InvalidArgumentException('Either username or id must be provided');
        }

        return $stmt->rowCount() > 0;
    }

    /* ---------- Query untuk halaman publik ---------- */

    public function count_members(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM members')->fetchColumn();
    }

    /**
     * Daftar member untuk halaman direktori, urut dari yang paling lama bergabung.
     * $search dicocokkan ke nama, username, bio, lokasi, dan interest.
     * Setiap item berisi: id, name, username, bio, avatar, location, joined_at, interests[].
     */
    public function list_members(string $search = ''): array
    {
        $sql = 'SELECT m.id, m.name, m.username, m.bio, m.avatar, m.location, m.joined_at FROM members m';
        $params = [];

        $search = $this->limit_text(trim($search), 100);
        if ($search !== '') {
            $like = '%' . addcslashes($search, '\\%_') . '%';
            // Native prepared statement tidak boleh memakai nama parameter yang sama dua kali.
            $sql .= ' WHERE m.name LIKE :q1 OR m.username LIKE :q2 OR m.bio LIKE :q3 OR m.location LIKE :q4
                      OR EXISTS (SELECT 1 FROM member_interests i WHERE i.member_id = m.id AND i.interest LIKE :q5)';
            $params = ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => $like];
        }

        $sql .= ' ORDER BY m.joined_at ASC, m.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $interests = $this->interests_for(array_column($members, 'id'));
        foreach ($members as &$member) {
            $member['interests'] = $interests[(int) $member['id']] ?? [];
        }
        unset($member);

        return $members;
    }

    /**
     * Profil lengkap satu member: kolom members + interests[] + links[platform => url] + gallery[].
     * Gallery hanya berisi foto 'public' kecuali $include_private = true.
     */
    public function get_profile(string $username, bool $include_private = false): ?array
    {
        $member = $this->read_member($username);
        if ($member === null) {
            return null;
        }

        $id = (int) $member['id'];
        $member['interests'] = $this->interests_for([$id])[$id] ?? [];

        $stmt = $this->db->prepare('SELECT platform, url FROM member_links WHERE member_id = :id ORDER BY id ASC');
        $stmt->execute(['id' => $id]);
        $member['links'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $sql = 'SELECT id, image, thumbnail, caption, uploaded_at, visibility FROM member_gallery WHERE member_id = :id';
        if (!$include_private) {
            $sql .= " AND visibility = 'public'";
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY uploaded_at ASC, id ASC');
        $stmt->execute(['id' => $id]);
        $member['gallery'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $member;
    }

    /** @return array<int, string[]> member_id => daftar interest (urut sesuai input). */
    private function interests_for(array $member_ids): array
    {
        if ($member_ids === []) {
            return [];
        }

        $ids = array_map('intval', array_values($member_ids));
        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        $stmt = $this->db->prepare(
            "SELECT member_id, interest FROM member_interests WHERE member_id IN ({$placeholders}) ORDER BY id ASC"
        );
        $stmt->execute($ids);

        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[(int) $row['member_id']][] = $row['interest'];
        }

        return $result;
    }

    /* ---------- Validasi (dipakai CoreMember & UpdateMember) ---------- */

    /**
     * Bersihkan & validasi field member. Field yang tidak dikirim diabaikan.
     * String kosong diubah jadi NULL. $require_identity = true mewajibkan name & username.
     */
    protected function clean_member_data(array $data, bool $require_identity): array
    {
        $clean = [];

        foreach (self::FIELDS as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field];
            if ($value !== null && !is_string($value)) {
                throw new InvalidArgumentException("Field '{$field}' harus berupa teks.");
            }

            $value = $value === null ? null : trim($value);
            if ($value === '') {
                $value = null;
            }

            // joined_at kosong = pakai default database (hari ini).
            if ($field === 'joined_at' && $value === null) {
                continue;
            }

            $clean[$field] = match ($field) {
                'name' => $this->check_length($this->required($value, 'name'), 'name', 100),
                'username' => $this->clean_username($value),
                'bio' => $value === null ? null : $this->check_length($value, 'bio', 255),
                'about' => $value === null ? null : $this->check_length($value, 'about', 5000),
                'location' => $value === null ? null : $this->check_length($value, 'location', 100),
                'avatar', 'cover' => $this->clean_url($value, $field),
                'joined_at' => $this->clean_date($value),
            };
        }

        if ($require_identity) {
            foreach (['name', 'username'] as $field) {
                if (!isset($clean[$field])) {
                    throw new InvalidArgumentException("Field '{$field}' wajib diisi.");
                }
            }
        }

        return $clean;
    }

    protected function required(?string $value, string $field): string
    {
        if ($value === null) {
            throw new InvalidArgumentException("Field '{$field}' wajib diisi.");
        }

        return $value;
    }

    protected function check_length(string $value, string $field, int $max): string
    {
        // Jumlah karakter (bukan byte), sama seperti VARCHAR(n) di MySQL. Tidak butuh ext-mbstring.
        $length = preg_match_all('/./su', $value);
        if ($length === false) {
            throw new InvalidArgumentException("Field '{$field}' harus berupa teks UTF-8 yang valid.");
        }
        if ($length > $max) {
            throw new InvalidArgumentException("Field '{$field}' maksimal {$max} karakter.");
        }

        return $value;
    }

    /** Potong teks ke $max karakter (aman untuk UTF-8; teks rusak jadi string kosong). */
    protected function limit_text(string $value, int $max): string
    {
        return preg_match('/^.{0,' . $max . '}/su', $value, $match) === 1 ? $match[0] : '';
    }

    protected function clean_username(?string $value): string
    {
        $value = strtolower($this->required($value, 'username'));

        // Harus cocok dengan route /member/{username} di public/.htaccess.
        if (!preg_match('/^[a-z0-9_-]{3,50}\z/', $value)) {
            throw new InvalidArgumentException("Username harus 3-50 karakter: huruf, angka, '_' atau '-'.");
        }

        return $value;
    }

    protected function clean_url(?string $value, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        $this->check_length($value, $field, 500);
        if (!self::is_http_url($value)) {
            throw new InvalidArgumentException("Field '{$field}' harus berupa URL http/https yang valid.");
        }

        return $value;
    }

    protected function clean_date(?string $value): string
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        if ($date === false || $date->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException("Field 'joined_at' harus berformat Y-m-d (contoh 2026-01-31).");
        }

        return $value;
    }

    protected static function is_http_url(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme)
            && in_array(strtolower($scheme), ['http', 'https'], true)
            && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /* ---------- Pengenal error MySQL ---------- */

    /** 1062 = duplicate entry (username sudah dipakai). */
    protected function is_duplicate(PDOException $e): bool
    {
        return ($e->errorInfo[1] ?? null) === 1062;
    }

    /** 1452 = foreign key gagal (member_id tidak ada). */
    protected function is_missing_member(PDOException $e): bool
    {
        return ($e->errorInfo[1] ?? null) === 1452;
    }
}
