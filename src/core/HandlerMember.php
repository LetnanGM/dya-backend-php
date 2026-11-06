<?php

declare(strict_types=1);

class CoreMember
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function insert_member(array $users_data): bool
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO members (name, username, bio) VALUES (:name, :username, :bio)");
        $stmt->bindValue(":name", $users_data['name'], PDO::PARAM_STR);
        $stmt->bindValue(":username", $users_data['username'], PDO::PARAM_STR);
        $stmt->bindValue(":bio", $users_data['bio'], PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        return $stmt;
    }

    public function read_member(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE username = :username");
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return null;
        }

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function delete_member(string $username = null, int $id = null): bool
    {
        if ($username === null && $id === null) {
            throw new InvalidArgumentException("Either username or id must be provided");
        }

        $stmt = $this->db->prepare("DELETE FROM members WHERE username = :username OR id = :id");
        $stmt->bindValue(":username", $username, PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}

class UpdateMember extends CoreMember
{
    private array $query;
    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    public update_this(string $user_id, ar)
}

?>