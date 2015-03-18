<?php

//PSR-4
spl_autoload_register(function ($class) {

    $prefix = 'ITrifonov\\PageViews\\';

    $baseDir = __DIR__ . '/';

    //not from our project namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);

    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    $file = str_replace(["/Modules/", "/Adapters/"], ["/modules/", "/adapters/"], $file);

    if (file_exists($file)) {
        require $file;
    }
});
