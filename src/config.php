<?php

declare(strict_types=1);

$site = require __DIR__ . '/data/site.php';

// Semua nilai bisa di-override lewat environment variable,
// contoh: MAIL_DRIVER=log php -S localhost:8000 -t public
return [
    // 'mail' = kirim lewat fungsi mail() PHP (butuh MTA/SMTP terkonfigurasi di server)
    // 'log'  = tulis pesan ke storage/messages.log (berguna untuk development lokal)
    'mail_driver' => getenv('MAIL_DRIVER') ?: 'mail',
    'mail_to'     => getenv('MAIL_TO') ?: $site['email'],
    'mail_from'   => getenv('MAIL_FROM') ?: 'no-reply@' . preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost'),

    // Jeda minimal (detik) antar pengiriman pesan dari satu sesi.
    'mail_throttle' => 30,

    'storage_dir' => dirname(__DIR__) . '/storage',
    'public_dir'  => dirname(__DIR__) . '/public',
];
