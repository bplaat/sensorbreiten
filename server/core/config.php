<?php

// Config
$_config = require_once ROOT . '/config.php';

function config(string $key) {
    global $_config;
    $value = $_config;
    $parts = explode('.', $key);
    foreach ($parts as $part) {
        $value = $value[$part];
    }
    return $value;
}

// Set up debug mode
if (config('app.debug')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Connect to database
Database::connect(
    config('database.connection') . ':' .
        'host=' . config('database.host') . ';' .
        'port=' . config('database.port') . ';' .
        'dbname=' . config('database.name') . ';' .
        'charset=utf8mb4',
    config('database.user'),
    config('database.password')
);
