<?php

// Load modules
require_once ROOT . '/core/autoloader.php';
require_once ROOT . '/core/utils.php';
require_once ROOT . '/core/config.php';
require_once ROOT . '/core/validate.php';
require_once ROOT . '/core/view.php';
require_once ROOT . '/core/parse_user_agent.php';

// Load routes
$_routeFiles = glob(ROOT . '/routes/*');
foreach ($_routeFiles as $file) {
    if (!is_dir($file)) {
        require_once $file;
    }
}

// Run routes
Route::run();
