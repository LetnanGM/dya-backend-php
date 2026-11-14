<?php

/**
 * Router untuk dev server: `php -S localhost:8000 -t public public/router.php` (make dev).
 *
 * PHP built-in server tidak membaca .htaccess, jadi URL cantik seperti /member/arya
 * tidak ada di sana. File ini meniru RewriteRule di public/.htaccess.
 * Hanya dipakai saat development; di Apache tidak berpengaruh.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$target = null;

if (preg_match('~^/member/?$~', $path)) {
    $target = 'member/members.php';
} elseif (preg_match('~^/member/([a-zA-Z0-9_-]+)/?$~', $path, $m)) {
    $target = 'member/members.php';
    $_GET['username'] = $m[1];
} elseif (preg_match('~^/app/(login|register|dashboard)$~', $path, $m)) {
    $target = "app/{$m[1]}.php";
}

if ($target === null) {
    return false; // bukan URL cantik: biarkan php -S melayani file statis / .php apa adanya
}

$_SERVER['SCRIPT_NAME'] = '/' . $target;
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/' . $target;
chdir(dirname(__DIR__ . '/' . $target));
require __DIR__ . '/' . $target;
