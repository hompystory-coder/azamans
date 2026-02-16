<?php
/**
 * Admin Sitemap Functions
 * 관리자 Sitemap 설정 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * Sitemap 설정 핸들러
 */
function admin_sitemap_handler($controller, $action = null) {
    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $postAction = $_POST['action'] ?? $action;
        
        // Sitemap 설정 저장
        if ($postAction === 'save') {
            try {
                // 설정 업데이트
                updateSitemapConfig('sitemap_enabled', $_POST['sitemap_enabled'] ?? 'Y');
                updateSitemapConfig('sitemap_item_limit', $_POST['sitemap_item_limit'] ?? '50000');
                updateSitemapConfig('sitemap_bbs_enabled', $_POST['sitemap_bbs_enabled'] ?? 'Y');
                updateSitemapConfig('sitemap_news_enabled', $_POST['sitemap_news_enabled'] ?? 'Y');
                updateSitemapConfig('sitemap_bbs_list', $_POST['sitemap_bbs_list'] ?? '');
                updateSitemapConfig('sitemap_news_list', $_POST['sitemap_news_list'] ?? '');
                
                jsonResponse(['success' => true, 'message' => 'Sitemap 설정이 저장되었습니다.']);
                return;
            } catch (Exception $e) {
                jsonResponse(['success' => false, 'message' => '저장 실패: ' . $e->getMessage()]);
                return;
            }
        }
        
        // Sitemap 재생성
        if ($postAction === 'regenerate') {
            require_once APP_PATH . '/libs/SitemapService.php';
            
            try {
                // Sitemap 파일 재생성
                $results = SitemapService::generateAll();
                
                jsonResponse([
                    'success' => true, 
                    'message' => '모든 Sitemap이 재생성되었습니다.',
                    'results' => $results
                ]);
                return;
            } catch (Exception $e) {
                jsonResponse(['success' => false, 'message' => 'Sitemap 생성 실패: ' . $e->getMessage()]);
                return;
            }
        }
    }
    
    // Sitemap 설정 페이지 표시
    $data = [
        'title' => 'Sitemap 설정'
    ];
    $controller->renderView('admin/sitemap', $data);
}

/**
 * Sitemap 설정 업데이트 헬퍼 함수
 */
function updateSitemapConfig($key, $value) {
    $db = getDBConnection();
    
    $stmt = $db->prepare("
        INSERT INTO site_config (config_key, config_value, config_group) 
        VALUES (?, ?, 'sitemap')
        ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)
    ");
    
    $stmt->execute([$key, $value]);
}
