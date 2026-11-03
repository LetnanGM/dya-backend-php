<?php

declare(strict_types=1);
require_once __DIR__ . '/../database/koneksi.php';

// NOTE: `thumb_image` awalnya "https://placeholder.com" (itu alamat website,
// bukan gambar, jadi gambarnya broken). Diganti URL placeholder gambar sungguhan.
// Ganti dengan file di public/assets/images/ (mis. 'images/proyek-1.webp')
// atau URL penuh; asset() dan card akan menanganinya.
function getProjects(PDO $db): array
{
    $stmt = $db->query("SELECT thumb_image, name, description, techstack, linkview FROM projects LIMIT 3");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Decode JSON techstack to array
    foreach ($projects as &$project) {
        $project['techstack'] = json_decode($project['techstack'], true);
    }

    return $projects;
}

return getProjects($db);