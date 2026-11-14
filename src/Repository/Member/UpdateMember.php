<?php

declare(strict_types=1);
namespace App\Repository\Member;

use PDO;
use PDOException;
use InvalidArgumentException;
use DateTimeImmutable;
use Throwable;

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
