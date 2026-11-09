<?php

/**
 * Halaman profil satu member: header, about, interests, link, gallery.
 *
 * @var array $member hasil CoreMember::get_profile() (gallery sudah hanya foto public)
 */
?>
<section class="profile-page">

    <div class="container members-container">

        <a href="<?= e(url('member')) ?>" class="profile-back">
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

                        <a href="<?= e($url) ?>" target="_blank" rel="me noopener noreferrer" class="social-item">
                            <?= e($label) ?> &rarr;
                        </a>

                    <?php endforeach; ?>

                </div>

            </section>

        <?php endif; ?>


        <?php partial('components/member_gallery', ['photos' => $member['gallery']]); ?>


        <!-- Profile Footer -->

        <footer class="text-center mt-5 pt-4">

            <div class="member-eyebrow mb-2">
                DYA / MEMBER PROFILE
            </div>

            <p class="member-muted small">
                Everyone has a place on the internet.
            </p>

            <a href="<?= e(url('member')) ?>" class="profile-button small">
                Explore other members &rarr;
            </a>

        </footer>

    </div>

</section>