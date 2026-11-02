<?php

declare(strict_types=1);

/* ---------- Config & output ---------- */

function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    $config ??= require __DIR__ . '/config.php';
    return $config[$key] ?? $default;
}

/** Escape output HTML. Pakai untuk SEMUA data dinamis di template. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------- URL & asset ---------- */

/** Folder tempat public/index.php berada, relatif terhadap domain ('' bila di root). */
function base_path(): string
{
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    return $dir === '/' || $dir === '.' ? '' : rtrim($dir, '/');
}

function url(string $path = ''): string
{
    return base_path() . '/' . ltrim($path, '/');
}

/** URL file di public/assets/, dengan ?v=<mtime> supaya cache browser ikut ter-update. */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = config('public_dir') . '/assets/' . $path;
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . $path) . $version;
}

/** Terima URL penuh (http/https) apa adanya, selain itu anggap file di public/assets/. */
function image_src(string $path): string
{
    return preg_match('#^https?://#i', $path) ? $path : asset($path);
}

/* ---------- View rendering ---------- */

/** Data yang otomatis tersedia di semua view & partial (mis. $site, $teams, $projects). */
function shared(?array $set = null): array
{
    static $data = [];
    if ($set !== null) {
        $data = array_merge($data, $set);
    }
    return $data;
}

/** Tampilkan potongan template dari src/views/<name>.php. */
function partial(string $name, array $vars = []): void
{
    $file = dirname(__DIR__) . '/src/views/' . $name . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("View tidak ditemukan: {$name}");
    }
    (static function (string $__file, array $__vars): void {
        extract($__vars, EXTR_SKIP);
        require $__file;
    })($file, array_merge(shared(), $vars));
}

/** Render view di dalam layout utama. */
function view(string $name, array $data = []): void
{
    ob_start();
    partial($name, $data);
    $content = (string) ob_get_clean();

    partial('layout', ['content' => $content]);
}

/* ---------- Session helpers: CSRF & flash ---------- */

function csrf_token(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Ambil pesan flash sekali pakai (langsung dihapus setelah dibaca). */
function flash_pull(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
