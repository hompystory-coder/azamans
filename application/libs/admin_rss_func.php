<?php
/**
 * Admin RSS Functions
 * 관리자 RSS 설정 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * RSS 설정 핸들러
 */
function admin_rss_handler($controller, $action = null) {
    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postAction = $_POST['action'] ?? $action;
        
        // RSS 설정 저장
        if ($postAction === 'save') {
            try {
                // 설정 업데이트
                updateConfig('rss_enabled', $_POST['rss_enabled'] ?? 'Y');
                updateConfig('rss_item_limit', $_POST['rss_item_limit'] ?? '100');
                updateConfig('rss_extract_days', $_POST['rss_extract_days'] ?? '30');
                updateConfig('rss_bbs_enabled', $_POST['rss_bbs_enabled'] ?? 'Y');
                updateConfig('rss_news_enabled', $_POST['rss_news_enabled'] ?? 'Y');
                updateConfig('rss_bbs_list', $_POST['rss_bbs_list'] ?? '');
                updateConfig('rss_news_list', $_POST['rss_news_list'] ?? '');
                
                jsonResponse(['success' => true, 'message' => 'RSS 설정이 저장되었습니다.']);
                return;
            } catch (Exception $e) {
                jsonResponse(['success' => false, 'message' => '저장 실패: ' . $e->getMessage()]);
                return;
            }
        }
        
        // RSS 재생성
        if ($postAction === 'regenerate') {
            require_once APP_PATH . '/libs/RssService.php';
            
            try {
                // RSS 파일 재생성
                $results = RssService::generateAll();
                
                jsonResponse([
                    'success' => true, 
                    'message' => '모든 RSS 피드가 재생성되었습니다.',
                    'results' => $results
                ]);
                return;
            } catch (Exception $e) {
                jsonResponse(['success' => false, 'message' => 'RSS 생성 실패: ' . $e->getMessage()]);
                return;
            }
        }
    }
    
    // RSS 설정 페이지 표시
    $data = [
        'title' => 'RSS 설정'
    ];
    $controller->renderView('admin/rss', $data);
}

/**
 * 설정 업데이트 헬퍼 함수
 */
function updateConfig($key, $value) {
    $db = getDBConnection();
    
    $stmt = $db->prepare("
        INSERT INTO site_config (config_key, config_value, config_group) 
        VALUES (?, ?, 'rss')
        ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)
    ");
    
    $stmt->execute([$key, $value]);
}
