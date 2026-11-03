<?php
require_once __DIR__ . '/../../src/core/AuthLogin.php';
require_once __DIR__ . '/../../src/database/koneksi.php';
$auth = new AuthLogin($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($auth->login($username, $password)) {
        header('Location: /app/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>

    <div><?php if (isset($error)) echo $error; ?></div>
</form>