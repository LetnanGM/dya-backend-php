<?php

/**
 * Halaman direktori member: header + pencarian + grid kartu.
 *
 * @var array  $members hasil CoreMember::list_members() (sudah terfilter)
 * @var int    $total   jumlah seluruh member (untuk penghitung "x / total")
 * @var string $search  kata kunci pencarian saat ini
 */
?>
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

<section class="directory-section">

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
                <?= count($members) ?> / <?= $total ?>
            </span>

        </div>

        <?php if (!empty($members)): ?>

            <div class="row g-4">

                <?php foreach ($members as $member): ?>

                    <div class="col-12 col-sm-6 col-lg-4">

                        <article class="directory-card">

                            <div class="d-flex align-items-center gap-3">

                                <div class="directory-avatar">

                                    <?php if (!empty($member['avatar'])): ?>

                                        <img src="<?= e($member['avatar']) ?>" alt="<?= e($member['name']) ?>" loading="lazy">

                                    <?php else: ?>

                                        <?= e(initial($member['name'])) ?>

                                    <?php endif; ?>

                                </div>

                                <div class="overflow-hidden">

                                    <h3 class="directory-name">
                                        <?= e($member['name']) ?>
                                    </h3>

                                    <span class="directory-username">
                                        @<?= e($member['username']) ?>
                                    </span>

                                </div>

                            </div>

                            <p class="directory-bio">
                                <?= e($member['bio']) ?>
                            </p>

                            <?php if (!empty($member['interests'])): ?>

                                <div class="d-flex flex-wrap gap-2">

                                    <?php foreach ($member['interests'] as $interest): ?>

                                        <span class="interest-tag">
                                            <?= e($interest) ?>
                                        </span>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                            <div class="directory-footer">

                                <span>
                                    Joined <?= e(date('Y', strtotime($member['joined_at']))) ?>
                                </span>

                                <a href="/member/<?= rawurlencode($member['username']) ?>" class="profile-button">
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

                <a href="<?= e(url('member')) ?>" class="btn btn-outline-primary btn-sm">
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

</section>
