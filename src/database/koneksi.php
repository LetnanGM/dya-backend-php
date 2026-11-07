<?php

declare(strict_types=1);

/**
 * Koneksi database (PDO, satu instance per request).
 *
 * Semua nilai bisa di-override lewat environment variable, contoh:
 *   DB_HOST=127.0.0.1 DB_USER=dya DB_PASS=rahasia php -S localhost:8000 -t public
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $host = $_ENV['DB_HOST'] ?: 'localhost';
        $port = $_ENV['DB_PORT'] ?: '3306';
        $name = $_ENV['DB_NAME'] ?: 'dya_db';
        $user = $_ENV['DB_USER'] ?: 'root';
        $pass = $_ENV['DB_PASS'];
        $pass = $pass === false ? '' : $pass;
        $charset = $_ENV['DB_CHARSET'] ?: 'utf8mb4';

        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset={$charset}",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    return $pdo;
}

// Kompatibilitas: file lama (login, register, redirect, data/*) masih memakai $db.
$db = db();
