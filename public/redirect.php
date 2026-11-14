<?php

declare(strict_types=1);

use App\Core\SafeRedirect;

require dirname(__DIR__) . '/src/bootstrap.php';

$st = new SafeRedirect(dbConnect());

$st->redirect($_GET['l'] ?? null);
?>