<?php
// Test menu update handler

// 환경 설정 로드
require_once __DIR__ . '/application/config/config.php';
require_once __DIR__ . '/application/libs/_db.php';
require_once __DIR__ . '/application/libs/_common_func.php';
require_once __DIR__ . '/application/libs/admin_header_menu_func.php';
require_once __DIR__ . '/application/libs/controller.php';

// Mock Controller
class TestController extends Controller {
    public function renderJson($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// 테스트 데이터
$testMenuId = 2;
$testData = [
    'menu_name' => '테스트 메뉴',
    'menu_type' => 'page',
    'menu_link' => '',
    'is_active' => 'Y',
    'open_new_window' => 'N',
    'page_content' => '<h1>테스트 페이지 콘텐츠</h1><p>이것은 테스트입니다.</p>'
];

// 환경 시뮬레이션
$_SERVER['REQUEST_METHOD'] = 'POST';

// JSON 입력 시뮬레이션 (php://input은 읽을 수 없으므로 직접 처리)
$jsonInput = json_encode($testData);

echo "=== 메뉴 업데이트 테스트 ===\n";
echo "Menu ID: $testMenuId\n";
echo "Test Data: " . print_r($testData, true) . "\n";

// 현재 메뉴 확인
$currentMenu = getUidData('header_menu', $testMenuId);
echo "\n현재 메뉴 정보:\n";
print_r($currentMenu);

// 업데이트 실행
echo "\n=== 업데이트 실행 ===\n";

$updateData = [];
if (isset($testData['menu_name'])) $updateData['menu_name'] = cleanInput($testData['menu_name']);
if (isset($testData['menu_type'])) $updateData['menu_type'] = $testData['menu_type'];
if (isset($testData['menu_link'])) $updateData['menu_link'] = cleanInput($testData['menu_link']);
if (isset($testData['is_active'])) $updateData['is_active'] = $testData['is_active'];
if (isset($testData['open_new_window'])) $updateData['open_new_window'] = $testData['open_new_window'];

echo "Update Data:\n";
print_r($updateData);

$result = getDbUpdate('header_menu', $updateData, 'id = ?', [$testMenuId]);

echo "\nUpdate Result: " . ($result !== false ? "SUCCESS" : "FAILED") . "\n";
echo "Result value: " . var_export($result, true) . "\n";

// 업데이트 후 메뉴 확인
$updatedMenu = getUidData('header_menu', $testMenuId);
echo "\n업데이트 후 메뉴 정보:\n";
print_r($updatedMenu);

// 페이지 콘텐츠 업데이트
if (isset($testData['page_content'])) {
    echo "\n=== 페이지 콘텐츠 업데이트 ===\n";
    
    $pageExists = getDbCnt('menu_pages', 'menu_id = ?', [$testMenuId]);
    echo "Page exists: " . ($pageExists ? "YES" : "NO") . "\n";
    
    if ($pageExists) {
        $pageResult = getDbUpdate('menu_pages', 
            ['content' => $testData['page_content']], 
            'menu_id = ?', 
            [$testMenuId]
        );
        echo "Page update result: " . var_export($pageResult, true) . "\n";
    } else {
        $pageResult = getDbInsert('menu_pages', [
            'menu_id' => $testMenuId,
            'content' => $testData['page_content']
        ]);
        echo "Page insert result: " . var_export($pageResult, true) . "\n";
    }
}

echo "\n=== 테스트 완료 ===\n";
