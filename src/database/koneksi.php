<?php

$db_host = "localhost";
$db_port = 3306;
$db_username = "root";
$db_password = "";
$db_name = "dya_db";
$db_charset = "utf8mb4";

$dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=$db_charset";
$db_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$db = new PDO($dsn, $db_username, $db_password, $db_options);

?>