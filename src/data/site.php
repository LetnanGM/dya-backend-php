<?php
/**
 * there's basic information for showing at UI.
 * this information is used in various parts of the application.
 * 
 * no database included cause this is a static information that doesn't change frequently.
 * from me github.com/LetnanGM
 */
declare(strict_types=1);
require_once __DIR__ . '/server.php';

$_data_server = getSelfInfo();

return [
    'site_title' => 'DyA - Grow Student',
    'name' => 'DyA - Teams',
    'url' => $_data_server['uri'],
    'social' => ['Github' => 'LetnanGM', 'Instagram' => '@syafiqueue'],
    'email' => 'letnan0gamedev@gmail.com',
    'companyName' => 'DyA',
    'cprojects' => 15,
    'exp' => 3,
];
