<?php

/**
 * Handler form kontak (pengganti endpoint /v1/email/send).
 *
 * - Dipanggil htmx (hx-post di form)       -> membalas potongan HTML (alert Bootstrap)
 * - Dipanggil fetch() dengan Accept: JSON   -> membalas JSON
 * - Atau via submit form biasa (tanpa JS)   -> redirect ke /#contact + pesan flash
 */

declare(strict_types=1);
use App\Repository\HandlerMail;

require dirname(__DIR__) . '/src/bootstrap.php';

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

/** Akhiri request: HTML untuk htmx, JSON untuk fetch, redirect + flash untuk submit biasa. */
function respond(bool $ok, string $message, int $status = 200): never
{
    global $wantsJson;

    if (is_htmx()) {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        $text = $ok ? 'Success! ' . $message . ' (This message will close in 3 seconds.)' : $message;
        printf('<div class="alert alert-%s mb-0" role="alert">%s</div>', $ok ? 'success' : 'danger', e($text));
    } elseif ($wantsJson) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        flash_set($ok ? 'success' : 'error', $message);
        header('Location: ' . url('/') . '#contact', true, 303);
    }
    exit;
}

/** Buang CR/LF supaya input tidak bisa dipakai untuk header injection. */
function single_line(string $value): string
{
    return trim(preg_replace('/[\r\n]+/', ' ', $value) ?? '');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(false, 'Method not allowed.', 405);
}

if (!csrf_valid($_POST['csrf'] ?? null)) {
    respond(false, 'Sesi tidak valid. Muat ulang halaman lalu coba lagi.', 419);
}

// Honeypot: bot biasanya mengisi semua field. Pura-pura sukses agar bot tidak belajar.
if (trim((string) ($_POST['website'] ?? '')) !== '') {
    respond(true, 'Mail sent!');
}

// Throttle sederhana per sesi.
$throttle = (int) config('mail_throttle', 30);
if (isset($_SESSION['last_sent']) && time() - (int) $_SESSION['last_sent'] < $throttle) {
    respond(false, 'Terlalu cepat. Tunggu beberapa detik sebelum mengirim lagi.', 429);
}

$name = single_line((string) ($_POST['name'] ?? ''));
$email = single_line((string) ($_POST['email'] ?? ''));
$subject = single_line((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

$len = static fn(string $v): int => function_exists('mb_strlen') ? mb_strlen($v) : strlen($v);

if ($name === '' || $subject === '' || $message === '') {
    respond(false, 'Semua kolom wajib diisi.', 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Alamat email tidak valid.', 422);
}
if ($len($name) > 100 || $len($subject) > 150 || $len($message) > 5000) {
    respond(false, 'Salah satu kolom terlalu panjang.', 422);
}

$to = (string) config('mail_to');
$from = (string) config('mail_from');

$handler = new HandlerMail();
$sent = $handler->handleIncomingMail($name, $email, $subject, $message);

if (!$sent) {
    error_log('contact.php: gagal mengirim pesan (driver: ' . config('mail_driver') . ')');
    respond(false, 'Pesan gagal dikirim. Silakan coba lagi nanti atau hubungi lewat email langsung.', 500);
}

$_SESSION['last_sent'] = time();

respond(true, 'Mail sent!');
