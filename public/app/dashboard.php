<?php

declare(strict_types=1);
use App\Controller\Auth\AuthLogin;

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /app/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    AuthLogin::logout();
    header("Location: /app/login.php");
    exit();
}


echo "Yey! kamu berhasil login ke dashboard. Selamat datang, " . htmlspecialchars($_SESSION['username']) . "!";
echo "<form method='POST'><button type='submit'>Logout</button></form>";

?>