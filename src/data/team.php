<?php

declare(strict_types=1);
require_once __DIR__ . '/../database/koneksi.php';

// `profil_img` relatif terhadap public/assets/ (dipanggil lewat asset()).
// Set 'status' => false untuk menampilkan slot kosong "Future DyA Member".
function getMembers(PDO $db): array
{
    $stmt = $db->query("SELECT profil_img, name, peran, description, linkgithub, linklinkedin, linkinstagram FROM teams LIMIT 3");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map database fields to the expected array structure
    return array_map(function ($member) {
        return [
            'status' => true,
            'profil_img' => $member['profil_img'],
            'name' => $member['name'],
            'peran' => $member['peran'],
            'shortdesc' => $member['description'],
            'link' => [
                'github' => $member['linkgithub'],
                'linkedin' => $member['linklinkedin'],
                'instagram' => $member['linkinstagram'],
            ],
        ];
    }, $members);
}

return getMembers($db);