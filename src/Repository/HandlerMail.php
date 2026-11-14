<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;
use PDOException;

class HandlerMail
{
    private PDO $db;

    public function __construct()
    {
        $this->db = dbConnect();
    }

    private function sendMail(string $name, string $email, string $subject, string $message): bool
    {
        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO inbox_messages (name, email, subject, message) VALUES (:name, :email, :subject, :message)");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error sending mail: " . $e->getMessage());
            return false;
        }
    }

    public function getAllMessages(): array
    {
        try {
            $stmt = $this->db->query("SELECT * FROM inbox_messages ORDER BY created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching messages: " . $e->getMessage());
            return [];
        }
    }

    public function deleteMessage(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM inbox_messages WHERE id = :id");
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error deleting message: " . $e->getMessage());
            return false;
        }
    }

    public function getMessageById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM inbox_messages WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $message = $stmt->fetch(PDO::FETCH_ASSOC);
            return $message ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching message by ID: " . $e->getMessage());
            return null;
        }
    }

    public function handleIncomingMail(string $name, string $email, string $subject, string $message): bool
    {
        return $this->sendMail($name, $email, $subject, $message);
    }
}

?>