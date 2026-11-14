<?php

declare(strict_types=1);

namespace App\Core;

use Exception;
use PDO;

class TrustRedirect
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function insert_shortlink(string $shortlink, string $longlink): bool|string
    {
        $stmt = $this->db->prepare("INSERT INTO shortlinks (shortlink, longlink) VALUES (:shortlink, :longlink)");
        $stmt->bindParam(':shortlink', $shortlink);
        $stmt->bindParam(':longlink', $longlink);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->db->lastInsertId();
        } else {
            throw new Exception("Failed to insert shortlink.");
        }
    }

    public function update_shortlink(string $shortlink, string $longlink): bool
    {
        $stmt = $this->db->prepare("UPDATE shortlinks SET longlink = :longlink WHERE shortlink = :shortlink");
        $stmt->bindParam(':shortlink', $shortlink);
        $stmt->bindParam(':longlink', $longlink);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception("Failed to update shortlink.");
        }

        return true;
    }

    public function delete_shortlink(string $shortlink): bool
    {
        $stmt = $this->db->prepare("DELETE FROM shortlinks WHERE shortlink = :shortlink");
        $stmt->bindParam(':shortlink', $shortlink);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            throw new Exception("Failed to delete shortlink.");
        }

        return true;
    }

    public function get_longlink(string $shortlink): ?string
    {
        $stmt = $this->db->prepare("SELECT longlink FROM shortlinks WHERE shortlink = :shortlink");
        $stmt->bindParam(':shortlink', $shortlink);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['longlink'] : null;
    }
}
