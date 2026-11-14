<?php

use App\Controller\Auth\AuthLogin;

$auth = new AuthLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $response = $auth->register($email, $username, $password);

    if ($response) {
        header('Location: /app/dashboard.php');
        exit;
    } else {
        $error = $response;
    }
}

?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>

    <div><?php if (isset($error))
        echo $error; ?></div>
</form>