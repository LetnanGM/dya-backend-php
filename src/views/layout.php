<?php
require_once __DIR__ . "/../core/HandlerSeo.php";
$seo = new SEOBuilder();

$seo
  ->set_title("DyA Teams")
  ->set_description("Technology and developer community.")
  ->set_keywords([
    "developer",
    "programming",
    "backend",
    "technology"
  ])
  ->set_author("DyA Teams")
  ->set_canonical("https://dya.web.id/")
  ->set_og([
    "site_name" => "DyA Teams",
    "image" => "https://dya.web.id/assets/img/og.png",
    "locale" => "id_ID"
  ])
  ->set_twitter([
    "site" => "@dyawebid"
  ]);


$seo->register_jsonld_template("organization", [
  "@context" => "https://schema.org",
  "@type" => "Organization",

  "name" => "{{name}}",
  "url" => "{{url}}",
  "logo" => "{{logo}}",

  "sameAs" => "{{social.links}}",

  "contactPoint" => [
    "@type" => "ContactPoint",
    "telephone" => "{{contact.telephone}}",
    "contactType" => "{{contact.type}}"
  ]
]);

$seo->add_jsonld_template("organization", [
  "name" => "DyA Teams",
  "url" => "https://dya.web.id/",
  "logo" => "https://dya.web.id/assets/img/logo.png",

  "social" => [
    "links" => [
      "https://github.com/LetnanGM",
      "https://instagram.com/syafiqueue"
    ]
  ],

  "contact" => [
    "telephone" => "+62-838-4980-4889",
    "type" => "customer service"
  ]
]);

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?= $seo->render() ?>
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
</head>

<body>
  <header><?php partial('components/navbar'); ?></header>
  <main id="maincontent">
    <?= $content ?>
  </main>
  <?php partial('partials/footer'); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
  <script src="<?= e(asset('js/script.js')) ?>" defer></script>
</body>

</html>