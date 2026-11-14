<?php

/**
 * ini adalah tempat Authentikasi terjadi antara login, daftar, dan logout.
 * wajib gunakan prepared untuk menghindari celah sql injection.
 * 
 * apabila database terbobol, segera tambal celah.
 */

declare(strict_types=1);

namespace App\Controller\Auth;

use App\Repository\User\UserManagement;

class AuthLogin
{
    private UserManagement $userhandler;

    public function __construct(?UserManagement $userhandler = null)
    {
        $this->userhandler = $userhandler ?? new UserManagement();
    }

    public function register(string $email, string $username, string $password): bool
    {
        return $this->userhandler->insert($email, $username, $password);
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->userhandler->GetUserByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }

        return false;
    }

    public static function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();

        session_destroy();
    }
}
