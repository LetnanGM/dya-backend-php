<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', getenv('APP_DEBUG') ? '1' : '0');

date_default_timezone_set('Asia/Jakarta');

require __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

shared([
    'site' => require __DIR__ . '/data/site.php',
    'teams' => require __DIR__ . '/data/team.php',
    'projects' => require __DIR__ . '/data/project.php',
    'members' => require __DIR__ . '/data/member.php',
]);
