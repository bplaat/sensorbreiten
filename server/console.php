<?php

// Load needed modules
define('ROOT', dirname(__FILE__));
require_once ROOT . '/core/autoloader.php';
require_once ROOT . '/core/config.php';

// Check if command is given
if (count($argv) >= 2) {
    // Migrate database models
    if ($argv[1] == 'migrate') {
        // Drop all models securely
        foreach (Model::modelNames() as $model) {
            ($model . '::dropTableSecure')();
        }

        // Create all models securely
        foreach (Model::modelNames() as $model) {
            ($model . '::createTableSecure')();
        }
    }

    // Seed database models securely
    if ($argv[1] == 'seed') {
        foreach (Model::modelNames() as $model) {
            ($model . '::seedTableSecure')();
        }
    }
}
