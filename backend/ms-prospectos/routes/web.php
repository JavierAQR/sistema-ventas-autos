<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-oci', function () {
    return [
        'php' => PHP_VERSION,
        'sapi' => php_sapi_name(),
        'ini' => php_ini_loaded_file(),
        'oci8' => extension_loaded('oci8'),
        'oci_connect' => function_exists('oci_connect'),
    ];
});
