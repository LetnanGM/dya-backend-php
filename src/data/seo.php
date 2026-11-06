<?php
// Mendapatkan nama file saat ini untuk identifikasi halaman
$current_page = basename($_SERVER['PHP_SELF']);

// Default Meta Tag jika halaman tidak terdaftar
$seo_title = "DyA Team | Grow Student";
$seo_desc = "Kami adalah tim pelajar yang dibentuk secara mandiri atas inisiatif pribadi, mampu menyelesaikan masalah dan memanfaatkan perkembangan teknologi bagi masa depan.";
$seo_keywords = "web developer, UI/UX design, custom php, bootstrap website, DyA, software house, pelajar berkembang, berkembang itu baik, web developer dengan php";
$seo_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$seo_image = "https://" . $_SERVER['HTTP_HOST'] . "/assets/img/og-default.jpg";

// Konfigurasi spesifik per halaman
switch ($current_page) {
    case 'index.php':
        $seo_title = "DyA Team | Jasa Pembuatan Website & Aplikasi Kustom";
        $seo_desc = "Transformasikan bisnis Anda bersama DyA. Spesialis kustom PHP, HTML5, dan Bootstrap dengan performa tinggi dan SEO-friendly.";

        break;

    case 'members.php':
        $seo_title = "Tentang Kami - Tim Di Balik DyA";
        $seo_desc = "Kenali pendiri, pengembang, dan desainer berbakat di DyA yang berdedikasi menciptakan produk digital berkualitas tinggi.";
        break;
}
?>