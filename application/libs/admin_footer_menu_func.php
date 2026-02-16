<?php
/**
 * Admin Footer Menu Management Functions
 * 푸터 메뉴 관리 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 푸터 메뉴 목록 조회
 */
function admin_footer_menu_handler($controller) {
    $menus = getDbArray("
        SELECT * FROM footer_menu 
        ORDER BY menu_order ASC, id ASC
    ") ?? [];
    
    $data = [
        'title' => '푸터 메뉴 관리',
        'menus' => $menus,
        'menu_type' => 'footer'
    ];
    
    $controller->renderView('admin/menu_footer', $data);
}

/**
 * 푸터 메뉴 생성 (콤마로 여러 개 생성 가능)
 */
function admin_footer_menu_create_handler($controller) {
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    $menuNameRaw = trim($_POST['menu_name'] ?? '');
    
    $menuNames = array_map('trim', explode(',', $menuNameRaw));
    
    if (empty($menuNames) || (count($menuNames) === 1 && $menuNames[0] === '')) {
        $controller->renderJson(['success' => false, 'message' => '메뉴명을 입력해주세요.']);
        return;
    }
    
    // footer_menu 테이블의 최대 순서 조회
    $maxOrderRow = getUidData("SELECT MAX(menu_order) as max_order FROM footer_menu WHERE parent_id = 0", []);
    $currentMaxOrder = $maxOrderRow['max_order'] ?? 0;
    
    $createdCount = 0;
    foreach ($menuNames as $menuName) {
        if ($menuName === '') continue;
        
        $currentMaxOrder++;
        $result = getDbInsert('footer_menu', [
            'parent_id' => 0,
            'menu_name' => $menuName,
            'menu_type' => 'page',
            'menu_order' => $currentMaxOrder,
            'is_active' => 'Y'
        ]);
        
        if ($result) {
            $createdCount++;
        } else {
        }
    }
    
    $message = $createdCount . '개의 푸터 메뉴가 생성되었습니다.';
    $controller->renderJson(['success' => true, 'message' => $message]);
}

/**
 * 푸터 메뉴 삭제
 */
function admin_footer_menu_delete_handler($controller, $id = null) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    // 메뉴 정보 조회
    $menu = getUidData("SELECT * FROM footer_menu WHERE id = ?", [$id]);
    
    if (!$menu) {
        $controller->renderJson(['success' => false, 'message' => '메뉴를 찾을 수 없습니다.']);
        return;
    }
    
    // page 타입이면 업로드 파일 및 PHP 파일 삭제
    if ($menu['menu_type'] === 'page') {
        // 업로드 파일 삭제
        $files = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ?", [$id]);
        foreach ($files as $file) {
            $filePath = BASE_PATH . '/public' . $file['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        getDbDelete('menu_page_upload', 'menu_id = ?', [$id]);
        
        // 페이지 콘텐츠 삭제
        getDbDelete('menu_pages', 'menu_id = ?', [$id]);
        
        // PHP 파일 삭제
        $phpFile = BASE_PATH . '/public/uploads/page/footer_' . $id . '.php';
        if (file_exists($phpFile)) {
            @unlink($phpFile);
        }
    }
    
    // 서브메뉴 조회 및 삭제
    $subMenus = getDbArray("SELECT * FROM footer_menu WHERE parent_id = ?", [$id]);
    foreach ($subMenus as $sub) {
        if ($sub['menu_type'] === 'page') {
            $files = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ?", [$sub['id']]);
            foreach ($files as $file) {
                $filePath = BASE_PATH . '/public' . $file['file_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            getDbDelete('menu_page_upload', 'menu_id = ?', [$sub['id']]);
            getDbDelete('menu_pages', 'menu_id = ?', [$sub['id']]);
            
            $phpFile = BASE_PATH . '/public/uploads/page/footer_' . $sub['id'] . '.php';
            if (file_exists($phpFile)) {
                @unlink($phpFile);
            }
        }
        getDbDelete('footer_menu', 'id = ?', [$sub['id']]);
    }
    
    // 메인 메뉴 삭제
    $result = getDbDelete('footer_menu', 'id = ?', [$id]);
    
    if ($result) {
        $controller->renderJson(['success' => true, 'message' => '푸터 메뉴가 삭제되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '푸터 메뉴 삭제에 실패했습니다.']);
    }
}

/**
 * 푸터 메뉴 순서 업데이트
 */
function admin_footer_menu_update_order_handler($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['items'])) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid data']);
        return;
    }
    
    try {
        foreach ($data['items'] as $item) {
            if (isset($item['id']) && isset($item['order'])) {
                getDbUpdate('footer_menu', 
                    ['menu_order' => $item['order']], 
                    'id = ?', 
                    [$item['id']]
                );
            }
        }
        $controller->renderJson(['success' => true, 'message' => '푸터 메뉴 순서가 업데이트되었습니다.']);
    } catch (Exception $e) {
        $controller->renderJson(['success' => false, 'message' => '순서 업데이트 실패: ' . $e->getMessage()]);
    }
}

/**
 * 푸터 서브메뉴 추가
 */
function admin_footer_submenu_add_handler($controller, $parentId = null) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$parentId) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    $submenuName = trim($_POST['submenu_name'] ?? '');
    
    if (empty($submenuName)) {
        $controller->renderJson(['success' => false, 'message' => '서브메뉴명을 입력해주세요.']);
        return;
    }
    
    // 부모 메뉴 조회
    $parentMenu = getUidData("SELECT * FROM footer_menu WHERE id = ?", [$parentId]);
    
    if (!$parentMenu) {
        $controller->renderJson(['success' => false, 'message' => '부모 메뉴를 찾을 수 없습니다.']);
        return;
    }
    
    // 현재 최대 순서 조회
    $maxOrderResult = getUidData("SELECT COALESCE(MAX(menu_order), 0) as max_order FROM footer_menu WHERE parent_id = ?", [$parentId]);
    $maxOrder = ($maxOrderResult['max_order'] ?? 0) + 1;
    
    $result = getDbInsert('footer_menu', [
        'parent_id' => $parentId,
        'menu_name' => $submenuName,
        'menu_type' => 'page',
        'menu_order' => $maxOrder,
        'is_active' => 'Y'
    ]);
    
    if ($result) {
        $controller->renderJson(['success' => true, 'message' => '서브메뉴가 추가되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '서브메뉴 추가에 실패했습니다.']);
    }
}

/**
 * 푸터 메뉴 수정 페이지
 */
function admin_footer_menu_edit_handler($controller, $id = null) {
    if (!$id) {
        header('Location: /admin/menu/footer');
        exit;
    }
    
    $menu = getUidData("SELECT * FROM footer_menu WHERE id = ?", [$id]);
    
    if (!$menu) {
        header('Location: /admin/menu/footer');
        exit;
    }
    
    // 페이지 정보 조회
    $page = null;
    if ($menu['menu_type'] === 'page') {
        $page = getUidData("SELECT * FROM menu_pages WHERE menu_id = ? AND menu_table = ?", [$id, 'footer']);
        if ($page) {
            $files = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ?", [$id]);
            $page['files'] = $files;
        }
    }
    
    // 게시판 목록 조회
    $boards = getDbArray("SELECT bbs_id, bbs_name FROM bbs_list ORDER BY bbs_name");
    
    // 뉴스 카테고리 목록 조회
    $newsList = getDbArray("SELECT news_id, news_name FROM news_list ORDER BY news_name");
    
    $data = [
        'title' => '푸터 메뉴 수정',
        'menu' => $menu,
        'page' => $page,
        'boards' => $boards,
        'newsList' => $newsList
    ];
    
    $controller->renderView('admin/menu_footer_edit', $data);
}

/**
 * 푸터 메뉴 업데이트
 */
function admin_footer_menu_update_handler($controller, $id = null) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    $updateData = [];
    
    if (isset($data['menu_name'])) $updateData['menu_name'] = cleanInput($data['menu_name']);
    if (isset($data['menu_type'])) $updateData['menu_type'] = $data['menu_type'];
    if (isset($data['menu_target'])) $updateData['menu_target'] = cleanInput($data['menu_target']);
    if (isset($data['menu_link'])) $updateData['menu_link'] = cleanInput($data['menu_link']);
    if (isset($data['custom_url'])) $updateData['custom_url'] = cleanInput($data['custom_url']);
    if (isset($data['target_window'])) $updateData['target_window'] = $data['target_window'];
    if (isset($data['use_redirect'])) $updateData['use_redirect'] = $data['use_redirect'];
    if (isset($data['is_hidden'])) $updateData['is_hidden'] = $data['is_hidden'];
    if (isset($data['is_blocked'])) $updateData['is_blocked'] = $data['is_blocked'];
    if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];
    if (isset($data['open_new_window'])) $updateData['open_new_window'] = $data['open_new_window'];
    
    $result = getDbUpdate('footer_menu', $updateData, 'id = ?', [$id]);
    
    error_log("Footer Menu Update - Result: " . var_export($result, true) . " (type: " . gettype($result) . ")");
    
    // false인 경우만 에러 (0은 정상 - 변경사항 없음)
    if ($result === false) {
        error_log("Footer Menu Update - FAILED: Database error");
        $controller->renderJson(['success' => false, 'message' => '푸터 메뉴 업데이트에 실패했습니다.']);
        return;
    }
    
    // 페이지 콘텐츠 업데이트
    if (isset($data['page_content'])) {
        error_log("Footer Menu Update - Processing page content");
        
        // FIXED: getDbCnt requires full SQL query
        $pageExists = getDbCnt("SELECT COUNT(*) FROM menu_pages WHERE menu_id = ? AND menu_table = ?", [$id, 'footer']);
        error_log("Footer Menu Update - Page exists: " . ($pageExists ? 'YES' : 'NO'));
        
        if ($pageExists) {
            $pageUpdateResult = getDbUpdate('menu_pages', 
                ['content' => $data['page_content']], 
                'menu_id = ? AND menu_table = ?', 
                [$id, 'footer']
            );
            error_log("Footer Menu Update - Page update result: " . var_export($pageUpdateResult, true));
        } else {
            $pageInsertResult = getDbInsert('menu_pages', [
                'menu_id' => $id,
                'menu_table' => 'footer',
                'content' => $data['page_content']
            ]);
            error_log("Footer Menu Update - Page insert result: " . var_export($pageInsertResult, true));
        }
        
        // PHP 파일 생성
        $phpContent = "<?php\n";
        $phpContent .= "// Auto-generated menu page\n";
        $phpContent .= "if (!defined('BASE_PATH')) exit('No direct script access allowed');\n";
        $phpContent .= "?>\n";
        $phpContent .= $data['page_content'];
        
        $phpFile = BASE_PATH . '/public/uploads/page/footer_' . $id . '.php';
        $uploadDir = dirname($phpFile);
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $phpFileWritten = file_put_contents($phpFile, $phpContent);
        error_log("Footer Menu Update - PHP file written: " . ($phpFileWritten !== false ? 'SUCCESS' : 'FAILED') . " to $phpFile");
    }
    
    error_log("Footer Menu Update - SUCCESS: Footer menu updated");
    $controller->renderJson(['success' => true, 'message' => '푸터 메뉴가 업데이트되었습니다.']);
}

/**
 * 푸터 메뉴 생성 (콤마로 여러 개 생성 가능)
 */

/**
 * 푸터 메뉴 페이지 파일 삭제 핸들러
 */
function admin_footer_menu_page_file_delete_handler($controller, $fileId = null) {
    if (!$fileId) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid file ID'], 400);
        return;
    }
    
    // 파일 정보 조회
    $file = getUidData("SELECT * FROM menu_page_upload WHERE uid = ?", [$fileId]);
    
    if (!$file) {
        $controller->renderJson(['success' => false, 'message' => '파일을 찾을 수 없습니다.'], 404);
        return;
    }
    
    // 실제 파일 삭제
    $realFilePath = BASE_PATH . '/' . ltrim($file['filepath'], '/');
    if (file_exists($realFilePath)) {
        if (!@unlink($realFilePath)) {
            error_log("첨부파일 삭제 실패: " . $realFilePath);
        } else {
            error_log("첨부파일 삭제 완료: " . $realFilePath);
        }
    }
    
    // DB에서 삭제
    $result = getDbDelete('menu_page_upload', 'uid = ?', [$fileId]);
    
    if ($result) {
        $controller->renderJson(['success' => true, 'message' => '파일이 삭제되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '파일 삭제에 실패했습니다.']);
    }
}
