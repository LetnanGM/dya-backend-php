<?php

declare(strict_types=1);
require_once __DIR__ . "/../database/koneksi.php";
require_once __DIR__ . "/RouteError.php";

class RoleChecker
{
    private PDO $db;
    private array $role = ["user", "admin", "owner"];

    public function __construct()
    {
        $this->db = dbConnect();
    }

    private function is_role(int $user_id, string $current_role): bool
    {
        $user_role = $this->db->prepare("SELECT role FROM users WHERE id = :user_id");
        $user_role->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $user_role->execute();

        $real_role = (string) $user_role;
        if ($current_role !== $real_role) {
            return false;
        }
        return true;
    }

    public function is_user()
    {
        return $this->is_role($_SESSION["user_id"], $this->role[0]);
    }
    public function is_admin()
    {
        return $this->is_role($_SESSION["user_id"], $this->role[1]);
    }
    public function is_owner()
    {
        return $this->is_role($_SESSION["user_id"], $this->role[2]);
    }
}

class UserManagement
{
    private PDO $db;

    public function __construct()
    {
        $this->db = dbConnect();
    }

    public function insert(string $email, string $username, string $password)
    {
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);
        $user = $this->db->prepare("INSERT IGNORE INTO users (email, username, password) VALUES(:email, :username, :password)");
        $user->bindValue(":email", $email, PDO::PARAM_STR);
        $user->bindValue(":username", $username, PDO::PARAM_STR);
        $user->bindValue(":password", $hashed_password, PDO::PARAM_STR);
        $user->execute();

        if ($user->rowCount() === 0) {
            echo "Username or Email already registered. <a href='/app/login.php'>Login</a>?";
            exit;
        }

        $user_data = [
            'id' => (int) $this->db->lastInsertId(),
            'username' => (string) $username
        ];

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION["user_id"] = $user_data["id"];
        $_SESSION["username"] = $user_data["username"];

        flash_set("success", "account created");
        return true;
    }

    public function update()
    {
        return;
    }
    public function delete()
    {
        return;
    }

    public function GetUserPagination(int $offset = 0, int $limit = 10): array
    {
        $pagination = $this->db->prepare("SELECT * FROM users ORDER BY id LIMIT :limit OFFSET :offset");
        $pagination->bindValue(":limit", $limit, PDO::PARAM_INT);
        $pagination->bindValue(":offset", $offset, PDO::PARAM_INT);
        $pagination->execute();

        return (array) $pagination->fetchAll(PDO::FETCH_ASSOC);
    }

    public function GetUserByID(int $user_id): array
    {
        $user = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $user->bindValue(":user", $user_id, PDO::PARAM_INT);
        $user->execute();

        return (array) $user->fetch(PDO::FETCH_ASSOC);
    }

    public function GetUserByUsername(string $username): array
    {
        $user = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $user->bindValue(":username", $username, PDO::PARAM_STR);
        $user->execute();

        return (array) $user->fetch(PDO::FETCH_ASSOC);
    }

    public function GetUserByEmail(string $email): array
    {
        $user = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $user->bindValue(":email", $email, PDO::PARAM_STR);
        $user->execute();

        return (array) $user->fetch(PDO::FETCH_ASSOC);
    }

    public function GetUserByRole(string $role): array
    {
        $user = $this->db->prepare("SELECT * FROM users WHERE role = :role");
        $user->bindValue(":role", $role, PDO::PARAM_STR);
        $user->execute();

        return (array) $user->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>