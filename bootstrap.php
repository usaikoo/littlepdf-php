<?php

declare(strict_types=1);

/**
 * Simple autoloader for development (when Composer is not available)
 */
spl_autoload_register(function ($class) {
    $prefix = 'LittlePdf\\';
    $baseDir = __DIR__ . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

