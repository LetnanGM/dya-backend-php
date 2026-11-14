<?php

/**
 * Gallery foto di profil member + markup lightbox. Script-nya (assets/js/lightbox.js) dimuat
 * sekali oleh layout dan bekerja lewat event delegation, jadi aman untuk konten hasil htmx.
 *
 * @var array $photos foto public milik member (CoreMember::get_profile()['gallery'])
 */
?>
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
            <?= count($photos) ?> PHOTOS
        </span>

    </div>

    <?php if (!empty($photos)): ?>

        <div class="gallery-grid">

            <?php foreach ($photos as $photo): ?>

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

<?php if (!empty($photos)): ?>

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

<?php endif; ?>
