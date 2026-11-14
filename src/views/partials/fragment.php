<?php

/**
 * Respons untuk request htmx: hanya isi halaman, tanpa navbar/footer/<head>.
 * <title> paling atas dipakai htmx untuk memperbarui judul tab browser.
 *
 * @var SeoHandler $seo
 * @var string     $content
 */
?>
<title><?= e($seo->title()) ?></title>
<?= $content ?>
