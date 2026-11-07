<?php

/**
 * ini adalah tempat Authentikasi terjadi antara login, daftar, dan logout.
 * wajib gunakan prepared untuk menghindari celah sql injection.
 * 
 * apabila database terbobol, segera tambal celah.
 */

declare(strict_types=1);

class AuthLogin
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function register(string $email, string $username, string $password)
    {
        $db = $this->db;
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        $stmt = $db->prepare("INSERT IGNORE INTO users (email, username, password) VALUES (:email, :username, :password)");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            echo "Username or Email already registered. <a href='/app/login.php'>Login</a>?";
            exit;
        }
        $user_data = [
            'id' => (int) $db->lastInsertId(),
            'username' => (string) $username
        ];

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['username'] = $user_data['username'];

        return true;
    }

    public function login(string $username, string $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        } else {
            return false;
        }
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

?>