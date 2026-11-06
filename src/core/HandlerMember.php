<?php

declare(strict_types=1);

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

class UpdateMember extends CoreMember
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Ubah sebagian field profil (hanya field yang dikirim yang diubah).
     *
     * @return bool true jika ada baris yang berubah; false jika member tidak ada
     *              atau nilainya sama dengan sebelumnya.
     * @throws InvalidArgumentException input tidak valid / username sudah dipakai.
     */
    public function update_member(int $id, array $fields): bool
    {
        $clean = $this->clean_member_data($fields, false);
        if ($clean === []) {
            throw new InvalidArgumentException('Tidak ada field yang diubah.');
        }

        $set = implode(', ', array_map(static fn(string $c): string => "{$c} = :{$c}", array_keys($clean)));

        try {
            $stmt = $this->db->prepare("UPDATE members SET {$set} WHERE id = :member_id");
            $stmt->execute($clean + ['member_id' => $id]);
        } catch (PDOException $e) {
            if ($this->is_duplicate($e)) {
                throw new InvalidArgumentException('Username sudah dipakai.', 0, $e);
            }
            throw $e;
        }

        return $stmt->rowCount() > 0;
    }

    /** Ganti seluruh daftar interest member (duplikat & string kosong dibuang). */
    public function set_interests(int $member_id, array $interests): void
    {
        $clean = [];
        foreach ($interests as $interest) {
            if (!is_string($interest)) {
                throw new InvalidArgumentException('Interest harus berupa teks.');
            }

            $interest = trim($interest);
            if ($interest === '') {
                continue;
            }

            $this->check_length($interest, 'interest', 100);
            $clean[strtolower($interest)] ??= $interest;
        }

        $this->in_transaction(function () use ($member_id, $clean): void {
            $this->db->prepare('DELETE FROM member_interests WHERE member_id = :id')
                ->execute(['id' => $member_id]);

            $insert = $this->db->prepare('INSERT INTO member_interests (member_id, interest) VALUES (:id, :interest)');
            foreach ($clean as $interest) {
                try {
                    $insert->execute(['id' => $member_id, 'interest' => $interest]);
                } catch (PDOException $e) {
                    // Duplikat versi collation database (mis. "Café" vs "cafe") dilewati.
                    if (!$this->is_duplicate($e)) {
                        throw $e;
                    }
                }
            }
        });
    }

    /** Ganti seluruh link sosial member. Format: ['GitHub' => 'https://github.com/...']. */
    public function set_links(int $member_id, array $links): void
    {
        $clean = [];
        foreach ($links as $platform => $url) {
            $platform = trim((string) $platform);
            if ($platform === '' || !is_string($url) || trim($url) === '') {
                continue;
            }

            $this->check_length($platform, 'platform', 50);
            $clean[strtolower($platform)] ??= [$platform, $this->clean_url(trim($url), "link {$platform}")];
        }

        $this->in_transaction(function () use ($member_id, $clean): void {
            $this->db->prepare('DELETE FROM member_links WHERE member_id = :id')
                ->execute(['id' => $member_id]);

            $insert = $this->db->prepare('INSERT INTO member_links (member_id, platform, url) VALUES (:id, :platform, :url)');
            foreach ($clean as [$platform, $url]) {
                try {
                    $insert->execute(['id' => $member_id, 'platform' => $platform, 'url' => $url]);
                } catch (PDOException $e) {
                    if (!$this->is_duplicate($e)) {
                        throw $e;
                    }
                }
            }
        });
    }

    /** Tambah foto ke gallery. @return int id foto baru. */
    public function add_gallery_photo(
        int $member_id,
        string $image,
        ?string $thumbnail = null,
        ?string $caption = null,
        string $visibility = 'public'
    ): int {
        if (!in_array($visibility, ['public', 'private'], true)) {
            throw new InvalidArgumentException("Visibility harus 'public' atau 'private'.");
        }

        $image = $this->clean_url(trim($image), 'image') ?? throw new InvalidArgumentException("Field 'image' wajib diisi.");
        $thumbnail = $this->clean_url($thumbnail === null ? null : (trim($thumbnail) ?: null), 'thumbnail');
        $caption = $caption === null ? null : (trim($caption) ?: null);
        if ($caption !== null) {
            $this->check_length($caption, 'caption', 255);
        }

        try {
            $this->db->prepare(
                'INSERT INTO member_gallery (member_id, image, thumbnail, caption, visibility)
                 VALUES (:member_id, :image, :thumbnail, :caption, :visibility)'
            )->execute([
                        'member_id' => $member_id,
                        'image' => $image,
                        'thumbnail' => $thumbnail,
                        'caption' => $caption,
                        'visibility' => $visibility,
                    ]);
        } catch (PDOException $e) {
            if ($this->is_missing_member($e)) {
                throw new InvalidArgumentException('Member tidak ditemukan.', 0, $e);
            }
            throw $e;
        }

        return (int) $this->db->lastInsertId();
    }

    /** Hapus satu foto. member_id ikut dicek supaya tidak bisa menghapus foto milik member lain. */
    public function delete_gallery_photo(int $member_id, int $photo_id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM member_gallery WHERE id = :photo AND member_id = :member');
        $stmt->execute(['photo' => $photo_id, 'member' => $member_id]);

        return $stmt->rowCount() > 0;
    }

    /** Jalankan $work dalam transaksi (ikut transaksi pemanggil bila sudah ada). */
    private function in_transaction(callable $work): void
    {
        $owns = !$this->db->inTransaction();
        if ($owns) {
            $this->db->beginTransaction();
        }

        try {
            $work();
            if ($owns) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($owns && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($e instanceof PDOException && $this->is_missing_member($e)) {
                throw new InvalidArgumentException('Member tidak ditemukan.', 0, $e);
            }
            throw $e;
        }
    }
}
