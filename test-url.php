<?php
// URL 파라미터 디버깅
header('Content-Type: text/plain');
echo "=== URL 디버깅 정보 ===\n\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set') . "\n";
echo "QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'not set') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'not set') . "\n\n";

echo "GET Parameters:\n";
print_r($_GET);

echo "\nSERVER Variables:\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'REQUEST') !== false || strpos($key, 'QUERY') !== false || strpos($key, 'SCRIPT') !== false) {
        echo "$key = $value\n";
    }
}
