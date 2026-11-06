<?php include_once('../data/seo.php'); ?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Standard Meta Tags -->
  <title><?php echo e($seo_title); ?></title>
  <meta name="description" content="<?php echo e($seo_desc); ?>">
  <meta name="keywords" content="<?php echo e($seo_keywords); ?>">
  <meta name="robots" content="index, follow">

  <!-- Canonical URL (Mencegah Duplikasi Konten) -->
  <link rel="canonical" href="<?php echo $seo_url; ?>">

  <!-- Open Graph / Facebook (Biar preview link di WA/FB jadi cakep) -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?php echo $seo_url; ?>">
  <meta property="og:title" content="<?php echo e($seo_title); ?>">
  <meta property="og:description" content="<?php echo e($seo_desc); ?>">
  <meta property="og:image" content="<?php echo $seo_image; ?>">

  <!-- Twitter Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo e($seo_title); ?>">
  <meta name="twitter:description" content="<?php echo e($seo_desc); ?>">
  <meta name="twitter:image" content="<?php echo $seo_image; ?>">

  <!-- Favicon -->
  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&family=Space+Grotesk:wght@500;700&display=swap" />

  <!-- Bootstrap 5.3 & Font Awesome 6 (CDN, dikunci dengan SRI) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.2/css/all.min.css"
    integrity="sha384-PPIZEGYM1v8zp5Py7UjFb79S58UeqCL9pYVnVPURKEqvioPROaVAJKKLzvH2rDnI" crossorigin="anonymous" />

  <!-- CSS situs (hasil kompilasi scss/main.scss) -->
  <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>" />

  <!-- Google Structured Data (JSON-LD Organization Schema) -->
  <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "DyA Teams",
      "url": "https://<?php echo $_SERVER['HTTP_HOST']; ?>",
      "logo": "https://<?php echo $_SERVER['HTTP_HOST']; ?>/assets/img/logo.png",
      "sameAs": [
        "https://github.com/LetnanGM",
        "https://instagram.com/syafiqueue",
      ],
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+62-838-4980-4889",
        "contactType": "customer service"
      }
    }
    </script>
</head>

<body>
  <header><?php partial('components/navbar'); ?></header>
  <main id="maincontent"><?= $content ?></main>
  <?php partial('partials/footer'); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <script src="<?= e(asset('js/script.js')) ?>" defer></script>
</body>

</html>