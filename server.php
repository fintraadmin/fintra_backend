<?php
/**
 * Simple PHP Development Server Router
 * This router is used for the built-in PHP web server
 * Usage: php -S localhost:8000 server.php
 */

// Get the requested file path
$requested_file = __DIR__ . $_SERVER['REQUEST_URI'];

// If it's a directory, serve index.php if it exists
if (is_dir($requested_file)) {
    $index_file = $requested_file . '/index.php';
    if (file_exists($index_file)) {
        require $index_file;
        return true;
    }
}

// If the file exists and is not a directory, serve it
if (is_file($requested_file) && file_exists($requested_file)) {
    return false; // Let the server handle static files
}

// Default to index.php for routing
require __DIR__ . '/index.php';
