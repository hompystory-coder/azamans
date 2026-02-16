<?php
// 테스트용 스크립트
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('BASE_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/application');
define('PUBLIC_PATH', __DIR__ . '/public');

// 필요한 함수들 로드
require_once APP_PATH . '/config/_db_info.php';
require_once APP_PATH . '/config/_db_func.php';
require_once APP_PATH . '/config/_sys.func.php';

// 테스트 데이터
$testData = [
    'menu_name' => 'Test Menu',
    'menu_type' => 'page',
    'page_content' => '<p>Test content</p>'
];

echo "=== Menu Update Test ===\n\n";

// 1. JSON 디코드 테스트
$jsonString = json_encode($testData);
echo "1. JSON Encode:\n";
echo $jsonString . "\n\n";

$decoded = json_decode($jsonString, true);
echo "2. JSON Decode:\n";
print_r($decoded);
echo "\n";

// 3. DB 함수 테스트
echo "3. Database Connection Test:\n";
try {
    $result = getUidData("SELECT * FROM header_menu WHERE id = ?", [2]);
    echo "Menu ID 2 found: " . ($result ? "YES" : "NO") . "\n";
    if ($result) {
        echo "Menu Name: " . $result['menu_name'] . "\n";
        echo "Menu Type: " . $result['menu_type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
