<?php

declare(strict_types=1);
require_once __DIR__ . '/../../helpers.php';
require_once __DIR__ . '/../../core/HandlerMember.php';
require_once __DIR__ . '/../../database/koneksi.php';

$memberRepo = new CoreMember(db());

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
    $member = $memberRepo->get_profile($slug);

    if ($member !== null) {
        $page = 'profile';
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

$filteredMembers = [];
$totalMembers = 0;

if ($page === 'directory') {
    $filteredMembers = $memberRepo->list_members($search);
    $totalMembers = $search === '' ? count($filteredMembers) : $memberRepo->count_members();
}


/*
|--------------------------------------------------------------------------
| 6. PROFILE DATA
|--------------------------------------------------------------------------
*/

// get_profile() hanya mengembalikan foto dengan visibility 'public'.
$gallery = $page === 'profile' ? $member['gallery'] : [];


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

<head>
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
</head>
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

                <input type="search" name="q" class="form-control" placeholder="Search by name, username, or interests..."
                    value="<?= e($search) ?>" aria-label="Search members">

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
                    <?= count($filteredMembers) ?> / <?= $totalMembers ?>
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
                                    data-caption="<?= e($photo['caption']) ?>" aria-label="View photo: <?= e($photo['caption']) ?>">

                                    <img src="<?= e($photo['thumbnail'] ?: $photo['image']) ?>" alt="<?= e($photo['caption']) ?>"
                                        loading="lazy">

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