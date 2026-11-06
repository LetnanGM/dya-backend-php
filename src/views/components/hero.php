<?php
/**
 * Hero — gaya company profile.
 *
 * Data yang dipakai (dari $site / $teams):
 *   companyName, cprojects, exp, email, social[Github|Instagram|LinkedIn]
 *   accepting_projects (opsional, bool; default false) -> teks status di pojok kiri atas
 *
 * Hook JavaScript (public/assets/js/script.js) yang HARUS tetap ada:
 *   #clock, #role-text, .hero-box + #spotlight, #marquee
 */

$accepting = !empty($site['accepting_projects']);

/**
 * Kemampuan utama
 * (Ubah di sini tanpa menyentuh markup.)
 * 
 * biar gak capek scroll yh :D
 */
$capabilities = [
  ['Backend & API Development', 'Server-side logic, databases, and clean, maintainable APIs.'],
  ['Web Applications', 'Responsive interfaces built with Bootstrap and JavaScript.'],
  ['Design Systems & Performance', 'Consistent, fast, and accessible front ends.'],
  ['Machine Learning', 'Junior-level ML experiments and prototypes.'],
];

/**
 * Angka kunci (KPI)
 *  - Projects delivered: jumlah proyek yang statusnya tidak kosong
 *  - Years active: tahun aktif (exp)
 */
$teamCount = count(array_filter($teams ?? [], static fn($m) => !empty($m['status'])));
$kpis = [
  [(string) $site['cprojects'] . '+', 'Projects delivered'],
  [(string) $site['exp'], 'Years active'],
  [(string) $teamCount, 'Team members'],
];

// Strip keahlian (marquee). Ditampilkan 2x agar animasi loop mulus semulus film biru. (Biar gak capek scroll ke atas buat ubah teks marquee, ubah di sini aja.)
$marquee = [
  'Backend Development',
  'Junior ML Development',
  'Junior Bootstrap',
  'JavaScript DOM',
  'Design Systems',
  'Web Performance',
];

// Tautan footer hero. Entri tanpa URL dilewati otomatis (mis. LinkedIn belum diisi wkwk :v).
$links = [
  ['GitHub', !empty($site['social']['Github']) ? 'https://github.com/' . $site['social']['Github'] : null, true],
  ['LinkedIn', !empty($site['social']['LinkedIn']) ? 'https://linkedin.com/in/' . $site['social']['LinkedIn'] : null, true],
  ['Instagram', !empty($site['social']['Instagram']) ? 'https://instagram.com/' . ltrim($site['social']['Instagram'], '@') : null, true],
  ['Email', !empty($site['email']) ? 'mailto:' . $site['email'] : null, false],
];
?>
<section id="home" class="hero-box position-relative border mx-3 mx-md-4 mt-3 d-flex flex-column mt-md-5"
  style="min-height: 88vh">
  <div class="spotlight" id="spotlight"></div>

  <div class="container-fluid px-4 px-md-5 py-4 d-flex flex-column flex-grow-1">
    <!-- Bar atas: status & lokasi -->
    <div
      class="d-flex justify-content-between align-items-center flex-wrap gap-2 font-mono text-uppercase small text-secondary">
      <span class="d-inline-flex align-items-center gap-2 border rounded-pill px-3 py-1">
        <span class="status-dot"></span>
        <?= $accepting ? 'Accepting new projects' : 'Not accepting new projects' ?>
      </span>
      <span>Semarang, ID <span class="text-muted">/</span>
        <span id="clock">--:--:--</span> WIB</span>
    </div>

    <!-- Konten utama -->
    <div class="row g-4 g-lg-5 align-items-center flex-grow-1 py-4 py-md-5">
      <div class="col-lg-7">
        <p class="font-mono text-uppercase small text-secondary mb-3">
          — <?= e($site['companyName']) ?> · Software Development Team
        </p>

        <h1 class="font-display fw-bold m-0" style="font-size: clamp(2.25rem, 5.5vw, 4.5rem); line-height: 1.05">
          We build<br />
          <span id="role-text" class="d-inline-block text-dark border-bottom border-dark border-3"
            style="padding-bottom: 0.04em">interfaces</span><span class="caret ms-1"
            style="width: 3px; height: 0.85em; vertical-align: baseline"></span><br />
          that actually work.
        </h1>

        <p class="mt-4 fs-5 text-secondary" style="max-width: 34rem">
          <?= e($site['companyName']) ?> is a software team delivering robust,
          clean, and maintainable backend systems, APIs, and web applications.
        </p>

        <div class="d-flex gap-2 flex-wrap mt-4">
          <a href="#contact" class="btn btn-dark rounded-pill px-4 py-2">Contact Us</a>
          <a href="#projects" class="btn btn-outline-dark rounded-pill px-4 py-2">View Our Work ↗</a>
        </div>
      </div>

      <!-- Panel layanan -->
      <div class="col-lg-5">
        <div class="border rounded-4 bg-white p-4">
          <p class="font-mono text-uppercase small text-secondary mb-2">
            What we do
          </p>
          <ol class="list-unstyled m-0">
            <?php foreach ($capabilities as $i => [$title, $desc]): ?>
              <li class="d-flex gap-3 py-3 border-top">
                <span class="font-mono small text-secondary pt-1"><?= sprintf('%02d', $i + 1) ?></span>
                <div>
                  <span class="d-block fw-semibold text-dark"><?= e($title) ?></span>
                  <span class="d-block small text-secondary"><?= e($desc) ?></span>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>

    <!-- Angka kunci -->
    <div class="row g-0 border rounded-3 bg-white">
      <?php foreach ($kpis as $i => [$value, $label]): ?>
        <div class="col-4 p-2 p-sm-3 p-md-4<?= $i < count($kpis) - 1 ? ' border-end' : '' ?>">
          <span class="d-block font-display fw-bold fs-3"><?= e($value) ?></span>
          <span class="font-mono text-uppercase small text-secondary"><?= e($label) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Strip keahlian -->
    <div class="marquee-wrap border-top mt-4 mt-md-5 py-3">
      <div class="marquee-track font-mono text-uppercase small text-secondary" id="marquee">
        <?php for ($copy = 0; $copy < 2; $copy++): ?>
          <span class="d-flex align-items-center gap-4" <?= $copy === 1 ? 'aria-hidden="true"' : '' ?>>
            <?php foreach ($marquee as $i => $item): ?>
              <?php if ($i % 2 === 0): ?>
                <b class="text-dark fw-medium"><?= e($item) ?></b> ·
              <?php else: ?>
                <?= e($item) ?> ·
              <?php endif; ?>
            <?php endforeach; ?>
          </span>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Footer hero: tautan & scroll cue -->
    <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-auto flex-wrap gap-2">
      <div class="d-flex gap-3 flex-wrap font-mono small text-secondary">
        <?php foreach ($links as [$label, $href, $external]): ?>
          <?php if ($href === null) {
            continue;
          } ?>
          <a href="<?= e($href) ?>" class="link-underline-grow text-secondary" <?= $external ? 'target="_blank" rel="noopener nofollow"' : '' ?>><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="d-none d-sm-block font-mono text-uppercase small text-secondary">
        Scroll <span class="bob">↓</span>
      </div>
    </div>
  </div>
</section>