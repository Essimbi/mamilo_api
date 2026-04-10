<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\Route;

try {
    $result = Route::prefix('v1');
    echo "Type of Route::prefix('v1'): " . gettype($result) . "\n";
    if (is_object($result)) {
        echo "Class of Route::prefix('v1'): " . get_class($result) . "\n";
    } else {
        echo "Value of Route::prefix('v1'): " . var_export($result, true) . "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
