<?php

declare(strict_types=1);
require_once __DIR__ . '/../../helpers.php';

// Ganti jika domain production berbeda.
$siteUrl = 'https://dya.web.id';
$siteName = 'DYA';

/**
 * Routing members URI
 * 
 * allowed :
 *  /members
 *  /member/arya
 *  /members.php
 *  /members.php?username=arya
 * 
 */
$requestPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
) ?: '/';

$slug = null;

if (
    preg_match(
        '~^/member/([a-zA-Z0-9_-]+)/?$~',
        $requestPath,
        $matches
    )
) {
    $slug = strtolower($matches[1]);
} elseif (isset($_GET['username']) && is_string($_GET['username'])) {
    $slug = strtolower(trim($_GET['username']));
}

$search = isset($_GET['q']) && is_string($_GET['q'])
    ? trim($_GET['q'])
    : '';

$page = 'directory';
$member = null;

if ($slug !== null && $slug !== '') {
    if (isset($members[$slug])) {
        $page = 'profile';
        $member = $members[$slug];
    } else {
        $page = 'not_found';
        http_response_code(404);
    }
}


/*
|--------------------------------------------------------------------------
| 5. DIRECTORY DATA
|--------------------------------------------------------------------------
*/

$filteredMembers = $members;

if ($search !== '' && $page === 'directory') {
    $filteredMembers = array_filter(
        $members,
        static function (array $item) use ($search): bool {
            $content = implode(' ', [
                $item['name'],
                $item['username'],
                $item['bio'],
                $item['location'] ?? '',
                implode(' ', $item['interests']),
            ]);

            return stripos($content, $search) !== false;
        }
    );
}


/*
|--------------------------------------------------------------------------
| 6. PROFILE DATA
|--------------------------------------------------------------------------
*/

$gallery = [];

if ($page === 'profile' && $member !== null) {
    $gallery = array_values(array_filter(
        $member['gallery'],
        static fn(array $photo): bool =>
            ($photo['visibility'] ?? 'private') === 'public'
    ));
}


/*
|--------------------------------------------------------------------------
| 7. SEO METADATA
|--------------------------------------------------------------------------
*/

$canonical = $page === 'profile'
    ? $siteUrl . '/member/' . rawurlencode($member['username'])
    : $siteUrl . '/member';

$metaTitle = match ($page) {
    'profile' => $member['name'] . ' (@' . $member['username'] . ') | DYA',
    'not_found' => 'Member Not Found | DYA',
    default => 'Members | DYA',
};

$metaDescription = match ($page) {
    'profile' => $member['bio'],
    'not_found' => 'The member profile you are looking for does not exist.',
    default => 'Meet the people behind DYA. Discover their profiles, interests, and stories.',
};

$metaImage = $page === 'profile'
    ? ($member['avatar'] ?: $member['cover'])
    : null;


/*
|--------------------------------------------------------------------------
| 8. STRUCTURED DATA
|--------------------------------------------------------------------------
*/

$structuredData = null;

if ($page === 'profile') {
    $structuredData = [
        '@context' => 'https://schema.org',
        '@type' => 'ProfilePage',
        'name' => $member['name'] . ' - DYA Member',
        'url' => $canonical,
        'mainEntity' => [
            '@type' => 'Person',
            'name' => $member['name'],
            'alternateName' => $member['username'],
            'description' => $member['bio'],
            'image' => $member['avatar'] ?: null,
            'url' => $canonical,
            'memberOf' => [
                '@type' => 'Organization',
                'name' => 'DYA',
                'url' => $siteUrl,
            ],
            'sameAs' => array_values(array_filter(
                $member['links'],
                'validUrl'
            )),
        ],
    ];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e($metaTitle) ?></title>

    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="profile">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($metaTitle) ?>">
    <meta property="og:description" content="<?= e($metaDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">

    <?php if ($metaImage): ?>
        <meta property="og:image" content="<?= e($metaImage) ?>">
    <?php endif; ?>

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($metaTitle) ?>">
    <meta name="twitter:description" content="<?= e($metaDescription) ?>">

    <?php if ($metaImage): ?>
        <meta name="twitter:image" content="<?= e($metaImage) ?>">
    <?php endif; ?>

    <?php if ($structuredData): ?>
        <script type="application/ld+json">
                                        <?= json_encode(
                                            $structuredData,
                                            JSON_UNESCAPED_SLASHES
                                            | JSON_UNESCAPED_UNICODE
                                            | JSON_HEX_TAG
                                            | JSON_HEX_AMP
                                            | JSON_HEX_APOS
                                            | JSON_HEX_QUOT
                                        ) ?>
                                    </script>
    <?php endif; ?>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/assets/css/main.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --member-border: var(--line);
            --member-muted: var(--color-muted);
            --member-surface: #fff;
        }

        body {
            background: var(--bg-light);
            color: var(--color-black);
        }

        a {
            text-decoration: none;
        }

        /* Shared */

        .members-container {
            max-width: 1100px;
        }

        .member-eyebrow {
            font-family: var(--font-code);
            font-size: .72rem;
            letter-spacing: .12em;
            color: var(--member-muted);
            text-transform: uppercase;
        }

        .member-muted {
            color: var(--member-muted);
        }

        .member-section-title {
            font-family: var(--font-heading);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
        }

        .member-section {
            margin-top: 65px;
        }

        /* Directory */

        .directory-header {
            padding: 100px 0 65px;
            text-align: center;
        }

        .directory-heading {
            font-family: var(--font-heading);
            font-size: clamp(2.5rem, 7vw, 4.5rem);
            font-weight: 700;
            letter-spacing: -.06em;
        }

        .directory-subtitle {
            max-width: 550px;
            margin: 0 auto;
            line-height: 1.8;
            color: var(--member-muted);
        }

        .directory-search {
            max-width: 470px;
            margin: 35px auto 0;
        }

        .directory-search input {
            min-height: 50px;
            padding: 0 17px;
            border: 1px solid var(--member-border);
            border-radius: 10px;
        }

        .directory-search input:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, .1);
        }

        .directory-section {
            padding-bottom: 100px;
        }

        .directory-count {
            font-family: var(--font-code);
            font-size: .75rem;
            color: var(--member-muted);
        }

        /* Member Cards */

        .directory-card {
            height: 100%;
            padding: 25px;
            border: 1px solid var(--member-border);
            border-radius: 16px;
            background: var(--member-surface);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .directory-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, .05);
        }

        .directory-avatar {
            width: 58px;
            height: 58px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-heading);
            font-size: 1.3rem;
            font-weight: 700;
        }

        .directory-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .directory-name {
            font-family: var(--font-heading);
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }

        .directory-username {
            font-family: var(--font-code);
            font-size: .75rem;
            color: var(--member-muted);
        }

        .directory-bio {
            min-height: 55px;
            margin: 20px 0;
            font-size: .87rem;
            line-height: 1.7;
            color: #666;
        }

        .interest-tag {
            display: inline-block;
            padding: 5px 9px;
            border: 1px solid var(--member-border);
            border-radius: 6px;
            color: #666;
            font-size: .7rem;
        }

        .directory-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid var(--member-border);
            font-size: .75rem;
            color: var(--member-muted);
        }

        .profile-button {
            color: var(--color-primary);
            font-weight: 600;
        }

        .profile-button:hover {
            text-decoration: underline;
        }

        /* Profile */

        .profile-page {
            padding: 55px 0 100px;
        }

        .profile-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--member-muted);
            font-size: .85rem;
            margin-bottom: 25px;
        }

        .profile-back:hover {
            color: var(--color-primary);
        }

        .profile-card {
            overflow: hidden;
            border: 1px solid var(--member-border);
            border-radius: 18px;
            background: #fff;
        }

        .profile-cover {
            position: relative;
            height: 260px;
            overflow: hidden;
            background:
                radial-gradient(circle at 20% 20%, #657ba3, transparent 45%),
                linear-gradient(120deg, #171d2b, #343b53);
        }

        .profile-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-cover::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom,
                    transparent 40%,
                    rgba(0, 0, 0, .15));
            pointer-events: none;
        }

        .profile-content {
            padding: 0 38px 35px;
        }

        .profile-identity {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-top: -52px;
            position: relative;
            z-index: 1;
            margin-bottom: 25px;
        }

        .profile-avatar {
            width: 110px;
            height: 110px;
            flex-shrink: 0;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 5px solid #fff;
            border-radius: 50%;
            background: #e9ecef;
            font-family: var(--font-heading);
            font-size: 2.5rem;
            font-weight: 700;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name {
            font-family: var(--font-heading);
            font-size: clamp(1.6rem, 4vw, 2.3rem);
            font-weight: 700;
            letter-spacing: -.04em;
            margin-bottom: 3px;
        }

        .profile-username {
            color: var(--member-muted);
            font-family: var(--font-code);
            font-size: .8rem;
        }

        .profile-bio {
            max-width: 680px;
            color: #666;
            line-height: 1.8;
        }

        .profile-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
            color: var(--member-muted);
            font-size: .8rem;
        }

        .profile-details span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        /* About */

        .profile-about {
            padding: 27px;
            border: 1px solid var(--member-border);
            border-radius: 14px;
            background: #fff;
            line-height: 1.85;
            color: #666;
        }

        .profile-interests {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        /* Social links */

        .profile-socials {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .social-item {
            padding: 9px 15px;
            border: 1px solid var(--member-border);
            border-radius: 8px;
            background: #fff;
            color: #444;
            font-size: .8rem;
            transition: border-color .2s ease, color .2s ease;
        }

        .social-item:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }

        /* Gallery */

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 13px;
        }

        .gallery-item {
            position: relative;
            display: block;
            overflow: hidden;
            aspect-ratio: 4 / 3;
            padding: 0;
            border: 0;
            border-radius: 12px;
            background: #e9ecef;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform .35s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.06);
        }

        .gallery-item::after {
            content: "View photo";
            position: absolute;
            inset: auto 0 0;
            padding: 25px 12px 12px;
            background: linear-gradient(transparent, rgba(0, 0, 0, .6));
            color: #fff;
            font-size: .75rem;
            opacity: 0;
            transition: opacity .2s ease;
        }

        .gallery-item:hover::after {
            opacity: 1;
        }

        .gallery-caption {
            margin-top: 7px;
            font-size: .8rem;
            color: var(--member-muted);
        }

        .gallery-empty {
            padding: 45px 20px;
            border: 1px dashed var(--member-border);
            border-radius: 14px;
            text-align: center;
            color: var(--member-muted);
        }

        /* Lightbox */

        .lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 25px;
            background: rgba(0, 0, 0, .94);
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox-content {
            max-width: min(100%, 1100px);
            max-height: 90vh;
            text-align: center;
        }

        .lightbox-content img {
            display: block;
            max-width: 100%;
            max-height: 78vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-caption {
            margin-top: 13px;
            color: #fff;
            font-size: .85rem;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 25px;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 50%;
            background: rgba(255, 255, 255, .08);
            color: #fff;
            font-size: 1.4rem;
            cursor: pointer;
        }

        /* 404 */

        .not-found {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 30px;
        }

        .not-found-code {
            font-family: var(--font-heading);
            font-size: clamp(5rem, 15vw, 9rem);
            font-weight: 700;
            letter-spacing: -.08em;
            line-height: 1;
        }

        /* Responsive */

        @media (max-width: 767px) {
            .directory-header {
                padding: 75px 0 45px;
            }

            .profile-cover {
                height: 180px;
            }

            .profile-content {
                padding: 0 20px 25px;
            }

            .profile-avatar {
                width: 85px;
                height: 85px;
                font-size: 2rem;
            }

            .profile-identity {
                gap: 13px;
            }

            .gallery-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px;
            }

            .member-section {
                margin-top: 45px;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .directory-card,
            .gallery-item img,
            .gallery-item::after {
                transition: none;
            }

            .directory-card:hover,
            .gallery-item:hover img {
                transform: none;
            }
        }
    </style>

</head>

<body>

    <?php if ($page === 'directory'): ?>

        <!-- ==========================================================
         DIRECTORY PAGE
    =========================================================== -->

        <header class="directory-header">

            <div class="container members-container">

                <div class="member-eyebrow mb-3">
                    DYA / PEOPLE DIRECTORY
                </div>

                <h1 class="directory-heading mb-3">
                    People behind DYA<span class="text-primary">.</span>
                </h1>

                <p class="directory-subtitle">
                    Every person has a story worth sharing.
                    Discover the people who are part of DYA.
                </p>

                <form method="GET" class="directory-search">

                    <input type="search" name="q" class="form-control"
                        placeholder="Search by name, username, or interests..." value="<?= e($search) ?>"
                        aria-label="Search members">

                </form>

            </div>

        </header>

        <main class="directory-section">

            <div class="container members-container">

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>
                        <h2 class="member-section-title mb-1">
                            The Members
                        </h2>

                        <span class="member-muted small">
                            Discover the people behind each profile.
                        </span>
                    </div>

                    <span class="directory-count">
                        <?= count($filteredMembers) ?> / <?= count($members) ?>
                    </span>

                </div>

                <?php if (!empty($filteredMembers)): ?>

                    <div class="row g-4">

                        <?php foreach ($filteredMembers as $item): ?>

                            <div class="col-12 col-sm-6 col-lg-4">

                                <article class="directory-card">

                                    <div class="d-flex align-items-center gap-3">

                                        <div class="directory-avatar">

                                            <?php if (!empty($item['avatar'])): ?>

                                                <img src="<?= e($item['avatar']) ?>" alt="<?= e($item['name']) ?>" loading="lazy">

                                            <?php else: ?>

                                                <?= e(initial($item['name'])) ?>

                                            <?php endif; ?>

                                        </div>

                                        <div class="overflow-hidden">

                                            <h3 class="directory-name">
                                                <?= e($item['name']) ?>
                                            </h3>

                                            <span class="directory-username">
                                                @<?= e($item['username']) ?>
                                            </span>

                                        </div>

                                    </div>

                                    <p class="directory-bio">
                                        <?= e($item['bio']) ?>
                                    </p>

                                    <?php if (!empty($item['interests'])): ?>

                                        <div class="d-flex flex-wrap gap-2">

                                            <?php foreach ($item['interests'] as $interest): ?>

                                                <span class="interest-tag">
                                                    <?= e($interest) ?>
                                                </span>

                                            <?php endforeach; ?>

                                        </div>

                                    <?php endif; ?>

                                    <div class="directory-footer">

                                        <span>
                                            Joined <?= e(date('Y', strtotime($item['joined_at']))) ?>
                                        </span>

                                        <a href="/member/<?= rawurlencode($item['username']) ?>" class="profile-button">
                                            View Profile &rarr;
                                        </a>

                                    </div>

                                </article>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="gallery-empty">

                        <h3 class="font-display fw-bold">
                            No members found.
                        </h3>

                        <p class="mb-3">
                            Try searching with another keyword.
                        </p>

                        <a href="/members" class="btn btn-outline-primary btn-sm">
                            Clear search
                        </a>

                    </div>

                <?php endif; ?>

                <footer class="text-center mt-5 pt-4">

                    <p class="member-muted small">
                        Everyone has a place on the internet.
                    </p>

                    <span class="member-eyebrow">
                        DYA MEMBERS DIRECTORY
                    </span>

                </footer>

            </div>

        </main>


    <?php elseif ($page === 'profile'): ?>

        <!-- ==========================================================
         INDIVIDUAL PROFILE PAGE
    =========================================================== -->

        <main class="profile-page">

            <div class="container members-container">

                <a href="/members" class="profile-back">
                    &larr; Back to members
                </a>

                <!-- Profile Header -->

                <article class="profile-card">

                    <div class="profile-cover">

                        <?php if (!empty($member['cover'])): ?>

                            <img src="<?= e($member['cover']) ?>" alt="">

                        <?php endif; ?>

                    </div>

                    <div class="profile-content">

                        <div class="profile-identity">

                            <div class="profile-avatar">

                                <?php if (!empty($member['avatar'])): ?>

                                    <img src="<?= e($member['avatar']) ?>" alt="<?= e($member['name']) ?>">

                                <?php else: ?>

                                    <?= e(initial($member['name'])) ?>

                                <?php endif; ?>

                            </div>

                            <div class="pb-2">

                                <h1 class="profile-name">
                                    <?= e($member['name']) ?>
                                </h1>

                                <div class="profile-username">
                                    @<?= e($member['username']) ?>
                                </div>

                            </div>

                        </div>

                        <p class="profile-bio mb-0">
                            <?= e($member['bio']) ?>
                        </p>

                        <div class="profile-details">

                            <?php if (!empty($member['location'])): ?>

                                <span>
                                    <span aria-hidden="true">&#9679;</span>
                                    <?= e($member['location']) ?>
                                </span>

                            <?php endif; ?>

                            <span>
                                Member since <?= e(formatDate($member['joined_at'])) ?>
                            </span>

                        </div>

                    </div>

                </article>


                <!-- About -->

                <section class="member-section">

                    <h2 class="member-section-title">
                        About
                    </h2>

                    <div class="profile-about">
                        <?= nl2br(e($member['about'])) ?>
                    </div>

                </section>


                <!-- Interests -->

                <?php if (!empty($member['interests'])): ?>

                    <section class="member-section">

                        <h2 class="member-section-title">
                            Interests
                        </h2>

                        <div class="profile-interests">

                            <?php foreach ($member['interests'] as $interest): ?>

                                <span class="interest-tag">
                                    <?= e($interest) ?>
                                </span>

                            <?php endforeach; ?>

                        </div>

                    </section>

                <?php endif; ?>


                <!-- Social Links -->

                <?php if (!empty($member['links'])): ?>

                    <section class="member-section">

                        <h2 class="member-section-title">
                            Around the Internet
                        </h2>

                        <div class="profile-socials">

                            <?php foreach ($member['links'] as $label => $url): ?>

                                <?php if (!validUrl($url))
                                    continue; ?>

                                <a href="<?= e($url) ?>" target="_blank" rel="noopener noreferrer" class="social-item">
                                    <?= e($label) ?> &rarr;
                                </a>

                            <?php endforeach; ?>

                        </div>

                    </section>

                <?php endif; ?>


                <!-- Gallery -->

                <section class="member-section">

                    <div class="d-flex align-items-center justify-content-between mb-4">

                        <div>
                            <h2 class="member-section-title mb-1">
                                Gallery
                            </h2>

                            <span class="member-muted small">
                                Moments worth remembering.
                            </span>
                        </div>

                        <span class="directory-count">
                            <?= count($gallery) ?> PHOTOS
                        </span>

                    </div>

                    <?php if (!empty($gallery)): ?>

                        <div class="gallery-grid">

                            <?php foreach ($gallery as $photo): ?>

                                <div>

                                    <button type="button" class="gallery-item" data-image="<?= e($photo['image']) ?>"
                                        data-caption="<?= e($photo['caption']) ?>"
                                        aria-label="View photo: <?= e($photo['caption']) ?>">

                                        <img src="<?= e($photo['thumbnail'] ?: $photo['image']) ?>"
                                            alt="<?= e($photo['caption']) ?>" loading="lazy">

                                    </button>

                                    <p class="gallery-caption">
                                        <?= e($photo['caption']) ?>
                                    </p>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="gallery-empty">

                            <h3 class="font-display fw-bold h5">
                                Nothing here yet.
                            </h3>

                            <p class="mb-0">
                                This member hasn't shared any public photos.
                            </p>

                        </div>

                    <?php endif; ?>

                </section>


                <!-- Profile Footer -->

                <footer class="text-center mt-5 pt-4">

                    <div class="member-eyebrow mb-2">
                        DYA / MEMBER PROFILE
                    </div>

                    <p class="member-muted small">
                        Everyone has a place on the internet.
                    </p>

                    <a href="/members" class="profile-button small">
                        Explore other members &rarr;
                    </a>

                </footer>

            </div>

        </main>


        <!-- Lightbox -->

        <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Photo viewer" aria-hidden="true">

            <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Close photo viewer">
                &times;
            </button>

            <div class="lightbox-content">

                <img src="" alt="" id="lightboxImage">

                <p class="lightbox-caption" id="lightboxCaption"></p>

            </div>

        </div>


    <?php else: ?>

        <!-- ==========================================================
         404 PAGE
    =========================================================== -->

        <main class="not-found">

            <div>

                <div class="not-found-code">
                    404<span class="text-primary">.</span>
                </div>

                <h1 class="font-display fw-bold h3 mt-4">
                    Member not found.
                </h1>

                <p class="member-muted">
                    The profile you're looking for doesn't exist.
                </p>

                <a href="/members" class="btn btn-primary mt-3">
                    Explore Members
                </a>

            </div>

        </main>

    <?php endif; ?>


    <?php if ($page === 'profile' && !empty($gallery)): ?>

        <script>
            (() => {
                const lightbox = document.getElementById('lightbox');
                const lightboxImage = document.getElementById('lightboxImage');
                const lightboxCaption = document.getElementById('lightboxCaption');
                const closeButton = document.getElementById('lightboxClose');

                let previousFocus = null;

                function openLightbox(image, caption, trigger) {
                    previousFocus = trigger;

                    lightboxImage.src = image;
                    lightboxImage.alt = caption;
                    lightboxCaption.textContent = caption;

                    lightbox.classList.add('active');
                    lightbox.setAttribute('aria-hidden', 'false');

                    document.body.style.overflow = 'hidden';
                    closeButton.focus();
                }

                function closeLightbox() {
                    lightbox.classList.remove('active');
                    lightbox.setAttribute('aria-hidden', 'true');

                    lightboxImage.src = '';
                    document.body.style.overflow = '';

                    previousFocus?.focus();
                }

                document.querySelectorAll('.gallery-item').forEach(button => {
                    button.addEventListener('click', () => {
                        openLightbox(
                            button.dataset.image,
                            button.dataset.caption,
                            button
                        );
                    });
                });

                closeButton.addEventListener('click', closeLightbox);

                lightbox.addEventListener('click', event => {
                    if (event.target === lightbox) {
                        closeLightbox();
                    }
                });

                document.addEventListener('keydown', event => {
                    if (!lightbox.classList.contains('active')) return;

                    if (event.key === 'Escape') {
                        closeLightbox();
                    }
                });
            })();
        </script>

    <?php endif; ?>

</body>

</html>