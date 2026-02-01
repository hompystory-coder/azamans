<?php
// 직접 데이터베이스 연결 및 메뉴 생성 테스트
require_once __DIR__ . '/../application/config/_env.func.php';
require_once __DIR__ . '/../application/config/_sys.func.php';

loadEnv(__DIR__ . '/../.env');

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'debug' => []];

try {
    // POST 데이터 확인
    $response['debug']['post_data'] = $_POST;
    $response['debug']['request_method'] = $_SERVER['REQUEST_METHOD'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['menu_name'])) {
        $menuName = trim($_POST['menu_name']);
        $response['debug']['menu_name'] = $menuName;
        
        if (!empty($menuName)) {
            // 데이터베이스 연결
            $db = getDbConnect();
            $response['debug']['db_connected'] = $db ? true : false;
            
            if ($db) {
                // 최대 순서 조회
                $result = $db->query("SELECT COALESCE(MAX(menu_order), 0) as max_order FROM header_menu");
                $maxOrder = $result->fetch_assoc()['max_order'];
                $response['debug']['max_order'] = $maxOrder;
                
                // 메뉴 삽입
                $newOrder = $maxOrder + 1;
                $stmt = $db->prepare("INSERT INTO header_menu (parent_id, menu_name, menu_type, menu_order, is_active) VALUES (0, ?, 'page', ?, 'Y')");
                $stmt->bind_param('si', $menuName, $newOrder);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = '메뉴가 생성되었습니다.';
                    $response['insert_id'] = $stmt->insert_id;
                } else {
                    $response['message'] = '메뉴 삽입 실패: ' . $stmt->error;
                }
                
                $stmt->close();
            } else {
                $response['message'] = '데이터베이스 연결 실패';
            }
        } else {
            $response['message'] = '메뉴명이 비어있습니다.';
        }
    } else {
        $response['message'] = 'POST 요청이 아니거나 menu_name이 없습니다.';
    }
} catch (Exception $e) {
    $response['message'] = '예외 발생: ' . $e->getMessage();
    $response['debug']['exception'] = $e->getTraceAsString();
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
