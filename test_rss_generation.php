<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/index.php';
require_once APP_PATH . '/libs/RssService.php';

echo "=== RSS Generation Test ===\n\n";

try {
    echo "Calling RssService::generateAll()...\n";
    $results = RssService::generateAll();
    echo "Success!\n\n";
    print_r($results);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
