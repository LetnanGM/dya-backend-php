<?php

/**
 * Meta tag SEO untuk <head>. Nilainya berasal dari $seo (SeoHandler, src/core/HandlerSEO.php).
 *
 * @var SeoHandler $seo
 */
$image = $seo->imageUrl();
?>
<title><?= e($seo->title()) ?></title>
<meta name="description" content="<?= e($seo->description()) ?>">
<meta name="keywords" content="<?= e($seo->keywords()) ?>">
<meta name="robots" content="<?= e($seo->robots()) ?>">
<link rel="canonical" href="<?= e($seo->canonicalUrl()) ?>">

<!-- Open Graph (preview link di WhatsApp / Facebook) -->
<meta property="og:type" content="<?= e($seo->type()) ?>">
<meta property="og:site_name" content="<?= e($seo->siteName()) ?>">
<meta property="og:url" content="<?= e($seo->canonicalUrl()) ?>">
<meta property="og:title" content="<?= e($seo->title()) ?>">
<meta property="og:description" content="<?= e($seo->description()) ?>">
<?php if ($image): ?>
<meta property="og:image" content="<?= e($image) ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="<?= $image ? 'summary_large_image' : 'summary' ?>">
<meta name="twitter:title" content="<?= e($seo->title()) ?>">
<meta name="twitter:description" content="<?= e($seo->description()) ?>">
<?php if ($image): ?>
<meta name="twitter:image" content="<?= e($image) ?>">
<?php endif; ?>

<!-- Structured Data (JSON-LD) -->
<?php foreach ($seo->schemas() as $schema): ?>
<script type="application/ld+json"><?= $seo->schemaJson($schema) ?></script>
<?php endforeach; ?>
