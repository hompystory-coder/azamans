<?php
/**
 * Admin Menu Functions
 * 관리자 메뉴 관리 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 메뉴 관리 핸들러
 * @param object $controller Admin controller instance
 * @param string $action Action name (header or footer)
 */
function admin_menu_handler($controller, $action = 'header') {
    $menuType = ($action === 'footer') ? 'footer' : 'header';
    
    // 메뉴 데이터 가져오기
    $menus = getDbArray("
        SELECT * FROM menu 
        WHERE menu_type = ? 
        ORDER BY sort_order ASC, id ASC
    ", [$menuType]);
    
    // 계층 구조로 변환
    $menuTree = admin_menu_build_tree($menus);
    
    $data = [
        'title' => ($menuType === 'footer' ? '푸터' : '헤더') . ' 메뉴 관리',
        'menus' => $menuTree,
        'menu_type' => $menuType
    ];
    
    $controller->renderView('admin/menu/' . $menuType, $data);
}

/**
 * 메뉴 트리 구조 생성
 */
function admin_menu_build_tree($menus, $parentId = 0) {
    $tree = [];
    foreach ($menus as $menu) {
        if ($menu['parent_id'] == $parentId) {
            $menu['children'] = admin_menu_build_tree($menus, $menu['id']);
            $tree[] = $menu;
        }
    }
    return $tree;
}

/**
 * 메뉴 생성 (헤더)
 */
function admin_menu_create($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $data = [
        'menu_type' => 'header',
        'parent_id' => (int)($_POST['parent_id'] ?? 0),
        'title' => cleanInput($_POST['title'] ?? ''),
        'url' => cleanInput($_POST['url'] ?? ''),
        'target' => cleanInput($_POST['target'] ?? '_self'),
        'icon' => cleanInput($_POST['icon'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active' => (int)($_POST['is_active'] ?? 1),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $menuId = getDbInsert('menu', $data);
    
    if ($menuId) {
        $controller->renderJson(['success' => true, 'message' => '메뉴가 생성되었습니다.', 'menu_id' => $menuId]);
    } else {
        $controller->renderJson(['success' => false, 'message' => '메뉴 생성에 실패했습니다.'], 500);
    }
}

/**
 * 메뉴 삭제
 */
function admin_menu_delete($controller, $id) {
    if (!$id) {
        $controller->renderJson(['success' => false, 'message' => 'ID가 지정되지 않았습니다.'], 400);
        return;
    }
    
    // 하위 메뉴가 있는지 확인
    $hasChildren = getDbCnt("SELECT COUNT(*) FROM menu WHERE parent_id = ?", [$id]);
    if ($hasChildren > 0) {
        $controller->renderJson(['success' => false, 'message' => '하위 메뉴가 있어 삭제할 수 없습니다.'], 400);
        return;
    }
    
    $deleted = getDbDelete('menu', 'id = ?', [$id]);
    
    if ($deleted) {
        $controller->renderJson(['success' => true, 'message' => '메뉴가 삭제되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '메뉴 삭제에 실패했습니다.'], 500);
    }
}

/**
 * 메뉴 순서 업데이트
 */
function admin_menu_update_order($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $orders = $input['orders'] ?? [];
    
    foreach ($orders as $order) {
        getDbUpdate('menu', 
            ['sort_order' => (int)$order['sort_order'], 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?',
            [$order['id']]
        );
    }
    
    $controller->renderJson(['success' => true, 'message' => '메뉴 순서가 업데이트되었습니다.']);
}

/**
 * 메뉴 수정
 */
function admin_menu_update($controller, $id) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    if (!$id) {
        $controller->renderJson(['success' => false, 'message' => 'ID가 지정되지 않았습니다.'], 400);
        return;
    }
    
    $data = [
        'title' => cleanInput($_POST['title'] ?? ''),
        'url' => cleanInput($_POST['url'] ?? ''),
        'target' => cleanInput($_POST['target'] ?? '_self'),
        'icon' => cleanInput($_POST['icon'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active' => (int)($_POST['is_active'] ?? 1),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $updated = getDbUpdate('menu', $data, 'id = ?', [$id]);
    
    if ($updated !== false) {
        $controller->renderJson(['success' => true, 'message' => '메뉴가 수정되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '메뉴 수정에 실패했습니다.'], 500);
    }
}

/**
 * 서브메뉴 추가
 */
function admin_menu_add_submenu($controller, $parentId) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    if (!$parentId) {
        $controller->renderJson(['success' => false, 'message' => '부모 메뉴 ID가 지정되지 않았습니다.'], 400);
        return;
    }
    
    // 부모 메뉴의 menu_type 가져오기
    $parent = getUidData("SELECT menu_type FROM menu WHERE id = ?", [$parentId]);
    if (!$parent) {
        $controller->renderJson(['success' => false, 'message' => '부모 메뉴를 찾을 수 없습니다.'], 404);
        return;
    }
    
    $data = [
        'menu_type' => $parent['menu_type'],
        'parent_id' => (int)$parentId,
        'title' => cleanInput($_POST['title'] ?? ''),
        'url' => cleanInput($_POST['url'] ?? ''),
        'target' => cleanInput($_POST['target'] ?? '_self'),
        'icon' => cleanInput($_POST['icon'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $menuId = getDbInsert('menu', $data);
    
    if ($menuId) {
        $controller->renderJson(['success' => true, 'message' => '서브메뉴가 추가되었습니다.', 'menu_id' => $menuId]);
    } else {
        $controller->renderJson(['success' => false, 'message' => '서브메뉴 추가에 실패했습니다.'], 500);
    }
}

/**
 * 푸터 메뉴 생성
 */
function admin_menu_create_footer($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $data = [
        'menu_type' => 'footer',
        'parent_id' => (int)($_POST['parent_id'] ?? 0),
        'title' => cleanInput($_POST['title'] ?? ''),
        'url' => cleanInput($_POST['url'] ?? ''),
        'target' => cleanInput($_POST['target'] ?? '_self'),
        'icon' => cleanInput($_POST['icon'] ?? ''),
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active' => (int)($_POST['is_active'] ?? 1),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $menuId = getDbInsert('menu', $data);
    
    if ($menuId) {
        $controller->renderJson(['success' => true, 'message' => '푸터 메뉴가 생성되었습니다.', 'menu_id' => $menuId]);
    } else {
        $controller->renderJson(['success' => false, 'message' => '푸터 메뉴 생성에 실패했습니다.'], 500);
    }
}
