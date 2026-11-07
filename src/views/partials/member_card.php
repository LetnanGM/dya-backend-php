<?php

/**
 * Kartu satu member di halaman direktori.
 *
 * @var array $member id, name, username, bio, avatar, joined_at, interests[]
 */
?>
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

        <a href="<?= e(url('member/' . rawurlencode($member['username']))) ?>" class="profile-button">
            View Profile &rarr;
        </a>

    </div>

</article>
