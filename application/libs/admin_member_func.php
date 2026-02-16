<?php
/**
 * Admin Member Functions
 * 관리자 회원 관리 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

function admin_members_list_handler($controller, $page = 1) {
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    
    // 검색 조건
    $search = cleanInput($controller->getParam('search', ''));
    $status = cleanInput($controller->getParam('status', ''));
    $level = cleanInput($controller->getParam('level', ''));
    
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(user_id LIKE ? OR name LIKE ? OR email LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    if (!empty($status)) {
        $where[] = "status = ?";
        $params[] = $status;
    }
    
    if (!empty($level)) {
        $where[] = "level = ?";
        $params[] = $level;
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    // 전체 개수
    $totalMembers = getDbCnt("SELECT COUNT(*) FROM member {$whereClause}", $params);
    $totalPages = ceil($totalMembers / $perPage);
    
    // 회원 목록
    $members = getDbArray("
        SELECT uid, user_id, name, email, level, point, post_count, comment_count, status, last_login, reg_date
        FROM member 
        {$whereClause}
        ORDER BY reg_date DESC 
        LIMIT {$perPage} OFFSET {$offset}
    ", $params);
    
    $data = [
        'title' => '회원 관리',
        'members' => $members,
        'total' => $totalMembers,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'search' => $search,
        'status' => $status,
        'level' => $level
    ];
    
    $controller->renderView('admin/members', $data);
}

/**
 * 회원 상세/수정
 */
function admin_member_detail_handler($controller, $uid, $action = '') {
    // 회원 정보 조회
    $member = getUidData("SELECT * FROM member WHERE uid = ?", [$uid]);
    
    if (!$member) {
        redirect('/admin/members');
        return;
    }
    
    // 액션 처리
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_member_update_handler($controller);
        return;
    } elseif ($action === 'reset-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_member_reset_password_handler($controller);
        return;
    } elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        admin_member_delete_handler($controller);
        return;
    }
    
    // 회원 상세 페이지
    $data = [
        'title' => '회원 정보',
        'member' => $member
    ];
    
    $controller->renderView('admin/member_detail', $data);
}

/**
 * 회원 정보 업데이트
 */
function admin_member_update_handler($controller) {
    $uid = cleanInput($_POST['uid'] ?? '');
    $updateData = [
        'name' => cleanInput($_POST['name'] ?? ''),
        'email' => cleanInput($_POST['email'] ?? ''),
        'level' => (int)($_POST['level'] ?? 1),
        'point' => (int)($_POST['point'] ?? 0),
        'status' => cleanInput($_POST['status'] ?? 'active')
    ];
    
    $result = getDbUpdate('member', $updateData, 'uid = ?', [$uid]);
    
    if ($result !== false) {
        $controller->renderJson(['success' => true, 'message' => '회원 정보가 수정되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '수정에 실패했습니다.'], 500);
    }
}

/**
 * 비밀번호 초기화
 */
function admin_member_reset_password_handler($controller) {
    $uid = cleanInput($_POST['uid'] ?? '');
    $newPassword = cleanInput($_POST['new_password'] ?? '');
    
    if (empty($newPassword) || strlen($newPassword) < 4) {
        $controller->renderJson(['success' => false, 'message' => '비밀번호는 4자 이상이어야 합니다.'], 400);
        return;
    }
    
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $result = getDbUpdate('member', ['password' => $hashedPassword], 'uid = ?', [$uid]);
    
    if ($result !== false) {
        $controller->renderJson(['success' => true, 'message' => '비밀번호가 초기화되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '초기화에 실패했습니다.'], 500);
    }
}

/**
 * 회원 삭제
 */
function admin_member_delete_handler($controller) {
    $uid = cleanInput($_POST['uid'] ?? '');
    
    $result = getDbDelete('member', 'uid = ?', [$uid]);
    
    if ($result) {
        $controller->renderJson(['success' => true, 'message' => '회원이 삭제되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '삭제에 실패했습니다.'], 500);
    }
}

/**
 * 회원가입 설정 핸들러
 */
function admin_joinconfig_handler($controller, $action = '') {
    // save 액션 처리
    if ($action === 'save') {
        admin_joinconfig_save_handler($controller);
        return;
    }
    
    // GET 요청: 설정 페이지 표시
    // var 파일에서 설정 로드
    $varFile = APP_PATH . '/config/var/member_join.var.php';
    $joinConfig = [];
    if (file_exists($varFile)) {
        include $varFile;
        $joinConfig = $join_config ?? [];
    }
    
    $data = [
        'title' => '회원가입 설정',
        'join_config' => $joinConfig,
        'terms_of_service' => getConfig('terms_of_service', ''),
        'privacy_policy' => getConfig('privacy_policy', ''),
        'youth_protection' => getConfig('youth_protection', '')
    ];
    $controller->renderView('admin/joinconfig', $data);
}

/**
 * 회원가입 설정 저장
 */
function admin_joinconfig_save_handler($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $configType = $_POST['config_type'] ?? '';
    
    if ($configType === 'join_env') {
        $useJoin = $_POST['use_join'] ?? 'Y';
        $approvalType = $_POST['approval_type'] ?? 'auto';
        $requiredFields = json_decode($_POST['required_fields'] ?? '[]', true);
        
        // 기본 필수 항목 추가
        if (!in_array('user_id', $requiredFields)) {
            $requiredFields[] = 'user_id';
        }
        if (!in_array('password', $requiredFields)) {
            $requiredFields[] = 'password';
        }
        
        // 설정 배열 생성
        $config = [
            'use_join' => $useJoin,
            'approval_type' => $approvalType,
            'required_fields' => $requiredFields
        ];
        
        // var 파일로 저장
        $varDir = APP_PATH . '/config/var';
        if (!is_dir($varDir)) {
            mkdir($varDir, 0755, true);
        }
        
        $varFile = $varDir . '/member_join.var.php';
        $varContent = "<?php\n";
        $varContent .= "// 회원가입 설정\n";
        $varContent .= "// 자동 생성 파일 - 수동으로 수정하지 마세요\n\n";
        $varContent .= "\$join_config = " . var_export($config, true) . ";\n";
        
        if (file_put_contents($varFile, $varContent)) {
            $controller->renderJson(['success' => true, 'message' => '가입 환경 설정이 저장되었습니다.']);
        } else {
            $controller->renderJson(['success' => false, 'message' => '파일 저장에 실패했습니다.']);
        }
    } else {
        $controller->renderJson(['success' => false, 'message' => '잘못된 설정 유형입니다.'], 400);
    }
}

/**
 * 회원 등급 관리 핸들러
 */
function admin_levels_handler($controller) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $levelData = [
                'level' => (int)$_POST['level'],
                'level_name' => cleanInput($_POST['level_name']),
                'point_min' => (int)($_POST['point_min'] ?? 0),
                'point_max' => (int)($_POST['point_max'] ?? 0)
            ];
            $result = getDbInsert('member_level', $levelData);
            $controller->renderJson(['success' => (bool)$result, 'message' => $result ? '등급이 추가되었습니다.' : '등급 추가 실패']);
            return;
        } elseif ($action === 'update') {
            $uid = (int)$_POST['uid'];
            $updateData = [
                'level_name' => cleanInput($_POST['level_name']),
                'point_min' => (int)($_POST['point_min'] ?? 0),
                'point_max' => (int)($_POST['point_max'] ?? 0)
            ];
            $result = getDbUpdate('member_level', $updateData, $uid);
            $controller->renderJson(['success' => (bool)$result, 'message' => $result ? '등급이 수정되었습니다.' : '등급 수정 실패']);
            return;
        } elseif ($action === 'delete') {
            $uid = (int)$_POST['uid'];
            $result = getDbDelete('member_level', $uid);
            $controller->renderJson(['success' => (bool)$result, 'message' => $result ? '등급이 삭제되었습니다.' : '등급 삭제 실패']);
            return;
        }
    }
    
    // 등급 목록 조회 (회원 수 포함)
    $levels = getDbArray("
        SELECT l.*, 
               COUNT(m.uid) as member_count
        FROM member_level l
        LEFT JOIN member m ON m.level = l.level AND m.status = 'active'
        GROUP BY l.uid, l.level, l.level_name, l.level_icon, l.point_min, l.point_max
        ORDER BY l.level ASC
    ");
    
    $data = [
        'title' => '회원 등급 관리',
        'levels' => $levels
    ];
    $controller->renderView('admin/levels', $data);
}

/**
 * 회원 포인트 지급 핸들러
 */
function admin_points_handler($controller) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $memberUid = (int)$_POST['member_uid'];
        $points = (int)$_POST['points'];
        $reason = cleanInput($_POST['reason'] ?? '관리자 지급');
        
        // 포인트 지급
        getDbUpdate('member', ['point' => "point + $points"], $memberUid);
        
        // 포인트 히스토리 기록
        $historyData = [
            'member_uid' => $memberUid,
            'points' => $points,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s')
        ];
        getDbInsert('point_history', $historyData);
        
        $controller->renderJson(['success' => true, 'message' => '포인트가 지급되었습니다.']);
        return;
    }
    
    $members = getDbArray("SELECT uid, user_id, name, point FROM member WHERE status = 'active' ORDER BY user_id ASC");
    $data = [
        'title' => '회원 포인트 지급',
        'members' => $members
    ];
    $controller->renderView('admin/points', $data);
}
