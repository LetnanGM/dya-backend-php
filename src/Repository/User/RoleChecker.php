<?php

declare(strict_types=1);

namespace App\Repository\User;

use PDO;

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
