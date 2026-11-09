<?php
/**
 * Kartu anggota tim.
 * Variabel: $profile (array dari src/data/team.php)
 */
$active = !empty($profile['status']);

$name  = $active ? ($profile['name'] ?? 'Your Name Here') : 'Your Name Here';
$role  = $active ? ($profile['peran'] ?? 'Future DyA Member') : 'Future DyA Member';
$about = $active
    ? ($profile['shortdesc'] ?? "We're looking for someone who wants to build, experiment, and create something together.")
    : "We're looking for someone who wants to build, experiment, and create something together.";

// Ikon Font Awesome per jenis tautan sosial.
$icons = [
    'github'    => 'fab fa-github',
    'linkedin'  => 'fab fa-linkedin-in',
    'instagram' => 'fab fa-instagram',
];
?>
<div class="col-md-4">
  <article class="team-member text-center p-4 h-100">
    <?php if ($active) : ?>
    <img
      src="<?= e(image_src($profile['profil_img'])) ?>"
      alt="<?= e($name) ?>"
      class="mb-4 shadow rounded-circle"
      width="120"
      height="120"
      loading="lazy"
      style="object-fit: cover"
    />
    <?php else : ?>
    <div
      class="team-placeholder mb-4 mx-auto shadow rounded-circle d-flex align-items-center justify-content-center"
      style="width: 120px; height: 120px"
    >
      <i class="fas fa-user-plus fa-2x"></i>
    </div>
    <?php endif; ?>

    <h5 class="mb-1"><?= e($name) ?></h5>

    <p class="text-muted mb-3"><?= e($role) ?></p>

    <p class="small mb-4"><?= e($about) ?></p>

    <div class="social-links d-flex justify-content-center gap-2">
      <?php if ($active) : ?>
        <?php foreach (($profile['link'] ?? []) as $network => $href) : ?>
          <?php if ($href === null || $href === '') { continue; } ?>
          <a
            href="<?= e($href) ?>"
            aria-label="<?= e($name) ?> on <?= e(ucfirst((string) $network)) ?>"
            class="btn btn-sm btn-outline-dark rounded-circle"
            rel="me noopener"
            target="_blank"
            <?php if (preg_match('#^https?://#i', (string) $href)) : ?>target="_blank" rel="noopener nofollow"<?php endif; ?>
          >
            <i class="<?= e($icons[strtolower((string) $network)] ?? 'fas fa-link') ?>"></i>
          </a>
        <?php endforeach; ?>
      <?php else : ?>
        <a
          href="#contact"
          aria-label="Join DYA"
          class="btn btn-sm btn-dark rounded-circle"
        >
          <i class="fas fa-arrow-right"></i>
        </a>
      <?php endif; ?>
    </div>
  </article>
</div>
