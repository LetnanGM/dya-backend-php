<?php
require_once __DIR__ . "/../core/HandlerSeo.php";
function register_all_template(SEOBuilder $seo)
{
    $seo->register_jsonld_template("organization", [
        "@context" => "https://schema.org",
        "@type" => "Organization",
        "name" => "{{name}}",
        "url" => "{{url}}",
        "logo" => "{{logo}}",
        "sameAs" => "{{sameAs}}",
        "contactPoint" => [
            "@type" => "ContactPoint",
            "telephone" => "{{telephone}}",
            "contactType" => "customer service"
        ]
    ]);
}

function getSeo(): SEOBuilder
{
    $seo_in = new SEOBuilder();
    register_all_template($seo_in);
    return $seo_in;
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
