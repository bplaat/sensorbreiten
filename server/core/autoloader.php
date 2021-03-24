<?php

$_autoloadFolders = [ ROOT . '/controllers', ROOT . '/core', ROOT . '/models' ];
function _searchAutoloadFolders(string $folder) {
    global $_autoloadFolders;
    $files = glob($folder . '/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            $_autoloadFolders[] = $file;
            _searchAutoloadFolders($file);
        }
    }
}
foreach ($_autoloadFolders as $folder) {
    _searchAutoloadFolders($folder);
}

spl_autoload_register(function (string $class) use ($_autoloadFolders) {
    foreach ($_autoloadFolders as $folder) {
        $path = $folder . '/' . $class . '.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
