<?php

declare(strict_types=1);

use App\Repository\Member\CoreMember;
use App\Repository\SEO\SeoHandler;
require_once __DIR__ . '/../../database/koneksi.php';

/**
 * Halaman member. File ini hanya mengurus: request -> data -> SEO -> pilih komponen.
 * Tampilannya ada di components/member_*.php dan partials/member_card.php.
 *
 * Route yang dikenali:
 *   /member                           direktori
 *   /member?q=gaming                  direktori + pencarian
 *   /member/arya                      profil
 *   /member/members.php?username=arya profil (tanpa rewrite, mis. php -S)
 *
 * @var SeoHandler $seo dibagikan oleh bootstrap.php
 */

$memberRepo = new CoreMember(dbConnect());

/* ---------- Request ---------- */

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$slug = null;
if (preg_match('~^/member/([a-zA-Z0-9_-]+)/?$~', $requestPath, $matches)) {
    $slug = strtolower($matches[1]);
} elseif (isset($_GET['username']) && is_string($_GET['username'])) {
    $slug = strtolower(trim($_GET['username']));
}

$search = isset($_GET['q']) && is_string($_GET['q']) ? trim($_GET['q']) : '';

/* ---------- Profil ---------- */

if ($slug !== null && $slug !== '') {
    $member = $memberRepo->get_profile($slug);

    if ($member === null) {
        http_response_code(404);
        $seo->set([
            'title' => 'Member Not Found | DYA',
            'description' => 'The member profile you are looking for does not exist.',
        ])->noindex();

        partial('components/member_not_found');
        return;
    }

    $canonical = $seo
        ->set([
            'title' => $member['name'] . ' (@' . $member['username'] . ') | DYA',
            'description' => $member['bio'],
            'image' => $member['avatar'] ?: $member['cover'],
            'type' => 'profile',
        ])
        ->canonical('member/' . rawurlencode($member['username']))
        ->canonicalUrl();

    $seo->schema([
        '@context' => 'https://schema.org',
        '@type' => 'ProfilePage',
        'name' => $member['name'] . ' - DYA Member',
        'url' => $canonical,
        'mainEntity' => array_filter([
            '@type' => 'Person',
            'name' => $member['name'],
            'alternateName' => $member['username'],
            'description' => $member['bio'],
            'image' => $member['avatar'],
            'url' => $canonical,
            'memberOf' => [
                '@type' => 'Organization',
                'name' => 'DYA',
                'url' => $seo->absoluteUrl(),
            ],
            'sameAs' => array_values(array_filter($member['links'], 'validUrl')),
        ]),
    ]);

    partial('components/member_profile', ['member' => $member]);
    return;
}

/* ---------- Direktori ---------- */

$members = $memberRepo->list_members($search);
$total = $search === '' ? count($members) : $memberRepo->count_members();

$seo->canonical('member');
if ($search !== '') {
    $seo->noindex(); // halaman hasil pencarian tidak perlu masuk indeks
}

partial('components/member_directory', [
    'members' => $members,
    'total' => $total,
    'search' => $search,
]);
