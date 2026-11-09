<?php

declare(strict_types=1);

/**
 * Konfigurasi SEO (data saja). Logikanya ada di src/core/HandlerSEO.php (class SeoHandler).
 *
 *  - defaults     : dipakai semua halaman.
 *  - pages        : override per halaman. Kuncinya nama file script tanpa .php
 *                   (index => public/index.php, members => public/member/members.php).
 *  - organization : sumber JSON-LD Organization yang dipasang di semua halaman.
 *
 * `image` boleh URL penuh atau path relatif terhadap public/assets/. Path relatif
 * hanya dipasang kalau filenya ada, jadi tidak ada og:image yang 404.
 */
$site = require __DIR__ . '/site.php';

// Handle di data/site.php ('social') -> URL profil untuk JSON-LD sameAs.
$profileBase = ['Github' => 'https://github.com/', 'Instagram' => 'https://instagram.com/'];
$sameAs = [];
foreach ($site['social'] as $platform => $handle) {
    if (isset($profileBase[$platform])) {
        $sameAs[] = $profileBase[$platform] . ltrim($handle, '@');
    }
}

return [
    'site_name' => $site['companyName'],

    'defaults' => [
        'title' => 'DyA Team | Grow Student',
        'description' => 'Kami adalah tim pelajar yang dibentuk secara mandiri atas inisiatif pribadi, mampu menyelesaikan masalah dan memanfaatkan perkembangan teknologi bagi masa depan.',
        'keywords' => 'web developer, UI/UX design, custom php, bootstrap website, DyA, software house, pelajar berkembang, berkembang itu baik, web developer dengan php',
        'image' => 'images/og-default.jpg',
        'type' => 'website',
        'robots' => 'index, follow',
    ],

    'pages' => [
        'index' => [
            'title' => 'DyA Team | Jasa Pembuatan Website & Aplikasi Kustom',
            'description' => 'Transformasikan bisnis Anda bersama DyA. Spesialis kustom PHP, HTML5, dan Bootstrap dengan performa tinggi dan SEO-friendly.',
        ],
        'members' => [
            'title' => 'Tentang Kami - Tim Di Balik DyA',
            'description' => 'Kenali pendiri, pengembang, dan desainer berbakat di DyA yang berdedikasi menciptakan produk digital berkualitas tinggi.',
        ],
    ],

    'organization' => [
        'name' => 'DyA Teams',
        'logo' => 'images/logo.png',
        'phone' => '+62-838-4980-4889',
        'same_as' => $sameAs,
    ],
];