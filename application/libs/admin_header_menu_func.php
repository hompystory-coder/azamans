<?php
/**
 * Admin Header Menu Management Functions
 * 헤더 메뉴 관리 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 헤더 메뉴 목록 조회
 */
function admin_header_menu_handler($controller) {
    $menus = getDbArray("
        SELECT * FROM header_menu 
        ORDER BY menu_order ASC, id ASC
    ") ?? [];
    
    $data = [
        'title' => '헤더 메뉴 관리',
        'menus' => $menus
    ];
    
    $controller->renderView('admin/menu_header', $data);
}

/**
 * 헤더 메뉴 생성 (콤마로 여러 개 생성 가능)
 */
function admin_header_menu_create_handler($controller) {
    error_log("createMenu called - REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST data: " . json_encode($_POST));
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request method'], 400);
        return;
    }
    
    $menuNames = trim($_POST['menu_name'] ?? '');
    $menuType = $_POST['menu_type'] ?? 'header';
    $tableName = 'header_menu';
    
    error_log("Menu names: " . $menuNames);
    error_log("Menu type: " . $menuType);
    error_log("Table name: " . $tableName);
    
    if (empty($menuNames)) {
        $controller->renderJson(['success' => false, 'message' => '메뉴명을 입력해주세요.']);
        return;
    }
    
    // 콤마로 분리
    $names = array_map('trim', explode(',', $menuNames));
    $names = array_filter($names); // 빈 값 제거
    
    if (empty($names)) {
        $controller->renderJson(['success' => false, 'message' => '유효한 메뉴명이 없습니다.']);
        return;
    }
    
    try {
        // 현재 최대 순서 조회
        $maxOrderResult = getUidData("SELECT COALESCE(MAX(menu_order), 0) as max_order FROM {$tableName}", []);
        $maxOrder = $maxOrderResult['max_order'] ?? 0;
        error_log("Max order: " . $maxOrder);
        
        $successCount = 0;
        foreach ($names as $name) {
            $maxOrder++;
            error_log("Inserting menu: $name with order: $maxOrder into $tableName");
            
            $result = getDbInsert($tableName, [
                'parent_id' => 0,
                'menu_name' => $name,
                'menu_type' => 'page',
                'menu_order' => $maxOrder,
                'is_active' => 'Y'
            ]);
            
            if ($result) {
                $successCount++;
                error_log("Menu inserted successfully: $name");
            } else {
                error_log("Failed to insert menu: $name");
            }
        }
        
        if ($successCount > 0) {
            $message = $successCount === 1 ? '메뉴가 생성되었습니다.' : "{$successCount}개의 메뉴가 생성되었습니다.";
            $controller->renderJson(['success' => true, 'message' => $message, 'count' => $successCount]);
        } else {
            $controller->renderJson(['success' => false, 'message' => '메뉴 생성에 실패했습니다.']);
        }
    } catch (Exception $e) {
        error_log("Error in createMenu: " . $e->getMessage());
        $controller->renderJson(['success' => false, 'message' => '오류: ' . $e->getMessage()]);
    }
}

/**
 * 헤더 메뉴 삭제
 */
function admin_header_menu_delete_handler($controller, $id = null) {
    if (!$id) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid menu ID'], 400);
        return;
    }
    
    // 1. 메뉴 정보 조회 (타입 확인용)
    $menu = getUidData("SELECT * FROM header_menu WHERE id = ?", [$id]);
    
    if (!$menu) {
        $controller->renderJson(['success' => false, 'message' => '메뉴를 찾을 수 없습니다.'], 404);
        return;
    }
    
    // 2. 페이지 타입이면 관련 데이터 삭제
    if ($menu['menu_type'] === 'page') {
        // 2-1. 첨부파일 실제 파일 삭제
        $pageFiles = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ?", [$id]);
        foreach ($pageFiles as $file) {
            $realFilePath = BASE_PATH . ltrim($file['filepath'], '/');
            if (file_exists($realFilePath)) {
                @unlink($realFilePath);
                error_log("첨부파일 삭제: " . $realFilePath);
            }
        }
        
        // 2-2. DB에서 첨부파일 레코드 삭제
        getDbDelete('menu_page_upload', 'menu_id = ?', [$id]);
        
        // 2-3. menu_pages 테이블에서 페이지 정보 삭제
        getDbDelete('menu_pages', 'menu_id = ?', [$id]);
        
        // 2-4. PHP 파일 삭제
        $phpFilePath = BASE_PATH . '/public/uploads/page/header_' . $id . '.php';
        if (file_exists($phpFilePath)) {
            @unlink($phpFilePath);
            error_log("PHP 파일 삭제: " . $phpFilePath);
        }
    }
    
    // 3. 서브메뉴 조회 및 삭제
    $subMenus = getDbArray("SELECT * FROM header_menu WHERE parent_id = ?", [$id]);
    foreach ($subMenus as $subMenu) {
        if ($subMenu['menu_type'] === 'page') {
            $subFiles = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ?", [$subMenu['id']]);
            foreach ($subFiles as $file) {
                $realFilePath = BASE_PATH . ltrim($file['filepath'], '/');
                if (file_exists($realFilePath)) {
                    @unlink($realFilePath);
                }
            }
            getDbDelete('menu_page_upload', 'menu_id = ?', [$subMenu['id']]);
            getDbDelete('menu_pages', 'menu_id = ?', [$subMenu['id']]);
            
            $phpFilePath = BASE_PATH . '/public/uploads/page/header_' . $subMenu['id'] . '.php';
            if (file_exists($phpFilePath)) {
                @unlink($phpFilePath);
            }
        }
        getDbDelete('header_menu', 'id = ?', [$subMenu['id']]);
    }
    
    // 4. 메인 메뉴 삭제
    $result = getDbDelete('header_menu', 'id = ?', [$id]);
    
    if ($result) {
        $controller->renderJson(['success' => true, 'message' => '메뉴가 삭제되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '메뉴 삭제에 실패했습니다.']);
    }
}

/**
 * 헤더 메뉴 순서 업데이트
 */
function admin_header_menu_update_order_handler($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => 'Invalid request method'], 400);
        return;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!isset($data['menus']) || !is_array($data['menus'])) {
        $controller->renderJson(['success' => false, 'message' => 'Invalid menu data']);
        return;
    }
    
    try {
        foreach ($data['menus'] as $menu) {
            if (isset($menu['id']) && isset($menu['order'])) {
                getDbUpdate('header_menu', 
                    ['menu_order' => $menu['order']], 
                    'id = ?', 
                    [$menu['id']]
                );
            }
        }
        $controller->renderJson(['success' => true, 'message' => '메뉴 순서가 업데이트되었습니다.']);
    } catch (Exception $e) {
        $controller->renderJson(['success' => false, 'message' => '순서 업데이트 실패: ' . $e->getMessage()]);
    }
}

/**
 * 헤더 메뉴 수정 페이지
 */
function admin_header_menu_edit_handler($controller, $id = null) {
    if (!$id) {
        header('Location: /admin/menu/header');
        exit;
    }
    
    $menu = getUidData("SELECT * FROM header_menu WHERE id = ?", [$id]);
    
    if (!$menu) {
        header('Location: /admin/menu/header');
        exit;
    }
    
    // 페이지 정보 조회
    $page = null;
    if ($menu['menu_type'] === 'page') {
        $page = getUidData("SELECT * FROM menu_pages WHERE menu_id = ? AND menu_table = ?", [$id, 'header']);
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
        'title' => '헤더 메뉴 수정',
        'menu' => $menu,
        'page' => $page,
        'boards' => $boards,
        'newsList' => $newsList
    ];
    
    $controller->renderView('admin/menu_header_edit', $data);
}

/**
 * 헤더 메뉴 업데이트
 */
function admin_header_menu_update_handler($controller, $id = null) {
    // Debug log file
    $debugLog = BASE_PATH . '/menu_update_debug.log';
    file_put_contents($debugLog, date('Y-m-d H:i:s') . " - Start update\n", FILE_APPEND);
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
        file_put_contents($debugLog, "ERROR: Invalid request - Method: " . $_SERVER['REQUEST_METHOD'] . ", ID: $id\n", FILE_APPEND);
        $controller->renderJson(['success' => false, 'message' => 'Invalid request'], 400);
        return;
    }
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // 디버그 로그
    file_put_contents($debugLog, "Menu Update - ID: $id\n", FILE_APPEND);
    file_put_contents($debugLog, "Menu Update - Input: " . $input . "\n", FILE_APPEND);
    file_put_contents($debugLog, "Menu Update - Decoded data: " . print_r($data, true) . "\n", FILE_APPEND);
    error_log("Menu Update - ID: $id");
    error_log("Menu Update - Input: " . $input);
    error_log("Menu Update - Decoded data: " . print_r($data, true));
    
    if (!$data) {
        file_put_contents($debugLog, "ERROR: Invalid JSON data\n", FILE_APPEND);
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
    
    $debugLog = BASE_PATH . '/menu_update_debug.log';
    file_put_contents($debugLog, "Menu Update - Update data: " . print_r($updateData, true) . "\n", FILE_APPEND);
    error_log("Menu Update - Update data: " . print_r($updateData, true));
    
    $result = getDbUpdate('header_menu', $updateData, 'id = ?', [$id]);
    
    file_put_contents($debugLog, "Menu Update - Result: " . var_export($result, true) . " (type: " . gettype($result) . ")\n", FILE_APPEND);
    error_log("Menu Update - Result: " . var_export($result, true) . " (type: " . gettype($result) . ")");
    
    // false인 경우만 에러 (0은 정상 - 변경사항 없음)
    if ($result === false) {
        file_put_contents($debugLog, "Menu Update - FAILED: Database error\n", FILE_APPEND);
        error_log("Menu Update - FAILED: Database error");
        $controller->renderJson(['success' => false, 'message' => '메뉴 업데이트에 실패했습니다.']);
        return;
    }
    
    // 페이지 콘텐츠 업데이트
    if (isset($data['page_content'])) {
        $debugLog = BASE_PATH . '/menu_update_debug.log';
        file_put_contents($debugLog, "Menu Update - Processing page content\n", FILE_APPEND);
        error_log("Menu Update - Processing page content");
        
        // FIXED: getDbCnt requires full SQL query
        $pageExists = getDbCnt("SELECT COUNT(*) FROM menu_pages WHERE menu_id = ? AND menu_table = ?", [$id, 'header']);
        file_put_contents($debugLog, "Menu Update - Page exists: " . ($pageExists ? 'YES' : 'NO') . " (count: $pageExists)\n", FILE_APPEND);
        error_log("Menu Update - Page exists: " . ($pageExists ? 'YES' : 'NO'));
        
        if ($pageExists) {
            $pageUpdateResult = getDbUpdate('menu_pages', 
                ['content' => $data['page_content']], 
                'menu_id = ? AND menu_table = ?', 
                [$id, 'header']
            );
            file_put_contents($debugLog, "Menu Update - Page update result: " . var_export($pageUpdateResult, true) . "\n", FILE_APPEND);
            error_log("Menu Update - Page update result: " . var_export($pageUpdateResult, true));
        } else {
            file_put_contents($debugLog, "Menu Update - Inserting new page with menu_id=$id, menu_table=header\n", FILE_APPEND);
            $pageInsertResult = getDbInsert('menu_pages', [
                'menu_id' => $id,
                'menu_table' => 'header',  // REQUIRED: 헤더 메뉴임을 명시
                'content' => $data['page_content']
            ]);
            file_put_contents($debugLog, "Menu Update - Page insert result: " . var_export($pageInsertResult, true) . " (type: " . gettype($pageInsertResult) . ")\n", FILE_APPEND);
            error_log("Menu Update - Page insert result: " . var_export($pageInsertResult, true));
            
            if ($pageInsertResult === false) {
                file_put_contents($debugLog, "ERROR: Page insert failed!\n", FILE_APPEND);
            } else {
                file_put_contents($debugLog, "SUCCESS: Page inserted with ID: $pageInsertResult\n", FILE_APPEND);
            }
        }
        
        // PHP 파일 생성
        $phpContent = "<?php\n";
        $phpContent .= "// Auto-generated menu page\n";
        $phpContent .= "if (!defined('BASE_PATH')) exit('No direct script access allowed');\n";
        $phpContent .= "?>\n";
        $phpContent .= $data['page_content'];
        
        $phpFile = BASE_PATH . '/public/uploads/page/header_' . $id . '.php';
        $uploadDir = dirname($phpFile);
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $phpFileWritten = file_put_contents($phpFile, $phpContent);
        error_log("Menu Update - PHP file written: " . ($phpFileWritten !== false ? 'SUCCESS' : 'FAILED') . " to $phpFile");
    }
    
    $debugLog = BASE_PATH . '/menu_update_debug.log';
    file_put_contents($debugLog, "Menu Update - SUCCESS: Menu updated\n\n", FILE_APPEND);
    error_log("Menu Update - SUCCESS: Menu updated");
    $controller->renderJson(['success' => true, 'message' => '메뉴가 업데이트되었습니다.']);
}

/**
 * 헤더 서브메뉴 추가
 */
function admin_header_submenu_add_handler($controller, $parentId = null) {
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
    $parentMenu = getUidData("SELECT * FROM header_menu WHERE id = ?", [$parentId]);
    
    if (!$parentMenu) {
        $controller->renderJson(['success' => false, 'message' => '부모 메뉴를 찾을 수 없습니다.']);
        return;
    }
    
    // 현재 최대 순서 조회
    $maxOrderResult = getUidData("SELECT COALESCE(MAX(menu_order), 0) as max_order FROM header_menu WHERE parent_id = ?", [$parentId]);
    $maxOrder = ($maxOrderResult['max_order'] ?? 0) + 1;
    
    $result = getDbInsert('header_menu', [
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
 * 메뉴 페이지 파일 삭제
 */
function admin_menu_page_file_delete_handler($controller, $fileId = null) {
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

/**
 * 헤더 메뉴 생성 (콤마로 여러 개 생성 가능)
 */
