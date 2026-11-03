<?php

declare(strict_types=1);
require_once __DIR__ . '/../database/koneksi.php';

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

class SafeRedirect extends TrustRedirect
{
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public function redirect(string $shortlink): void
    {
        $longlink = $this->get_longlink($shortlink);
        if ($longlink) {
            header("Location: " . $longlink);
            exit();
        } else {
            http_response_code(404);
            echo "Shortlink not found.";
        }
    }

    public function create_shortlink(string $shortlink, string $longlink): void
    {
        $this->insert_shortlink($shortlink, $longlink);
    }

    public function update_shortlink_entry(string $shortlink, string $longlink): void
    {
        $this->update_shortlink($shortlink, $longlink);
    }

    public function delete_shortlink_entry(string $shortlink): void
    {
        $this->delete_shortlink($shortlink);
    }

    public function get_longlink_entry(string $shortlink): ?string
    {
        return $this->get_longlink($shortlink);
    }
}

?>