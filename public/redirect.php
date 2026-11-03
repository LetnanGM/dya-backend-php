<?php

declare(strict_types=1);

require_once __DIR__ . "/../src/database/koneksi.php";
require_once __DIR__ . "/../src/core/Redirection.php";

$st = new SafeRedirect($db);

$st->redirect($_GET['l'] ?? null);
?>