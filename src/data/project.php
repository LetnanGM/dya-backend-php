<?php

declare(strict_types=1);

// NOTE: `thumb_image` awalnya "https://placeholder.com" (itu alamat website,
// bukan gambar, jadi gambarnya broken). Diganti URL placeholder gambar sungguhan.
// Ganti dengan file di public/assets/images/ (mis. 'images/proyek-1.webp')
// atau URL penuh; asset() dan card akan menanganinya.
return [
    [
        'thumb_image' => 'https://placehold.co/600x360?text=E-Commerce+API',
        'name'        => 'E-Commerce API REST',
        'shortdesc'   => 'A robust, scalable backend built with Node.js, Express, and PostgreSQL featuring JWT authentication and Stripe integration.',
        'techstack'   => ['Node.js', 'PostgreSQL'],
        'linkview'    => '#HEREISLINK',
    ],
    [
        'thumb_image' => 'https://placehold.co/600x360?text=Task+Service',
        'name'        => 'Task Management Service',
        'shortdesc'   => 'A microservice architecture application running on Docker, utilizing Redis queues for processing background email notifications.',
        'techstack'   => ['Python', 'Redis'],
        'linkview'    => '#HEREISLINK',
    ],
    [
        'thumb_image' => 'https://placehold.co/600x360?text=Analytics+Dashboard',
        'name'        => 'Real-time Analytics Dashboard',
        'shortdesc'   => 'An internal dashboard utilizing WebSockets to stream server health metrics and incoming server requests in real-time.',
        'techstack'   => ['Go', 'WebSockets'],
        'linkview'    => '#HEREISLINK',
    ],
];
