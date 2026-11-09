<?php

declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

error_reporting(E_ALL);
ini_set('display_errors', $_ENV["APP_DEBUG"] ? '1' : '0');

date_default_timezone_set($_ENV['APP_TIMEZONE']);

require __DIR__ . '/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require __DIR__ . '/core/HandlerSEO.php';

// Preset SEO dipilih dari nama script (index.php -> 'index', member/members.php -> 'members').
$seo = (new SeoHandler(
    require __DIR__ . '/data/seo.php',
    (string) config('site_url'),
    (string) config('public_dir'),
    $_SERVER['REQUEST_URI'] ?? '/'
))->page(basename($_SERVER['SCRIPT_NAME'] ?? 'index.php', '.php'));

shared([
    'seo' => $seo,
    'site' => require __DIR__ . '/data/site.php',
    'teams' => require __DIR__ . '/data/team.php',
    'projects' => require __DIR__ . '/data/project.php',
]);
