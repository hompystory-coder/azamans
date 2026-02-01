<?php
/**
 * Admin Controller
 * 관리자 페이지 컨트롤러
 */

class Admin extends Controller {
    
    public function __construct() {
        // 관리자 권한 체크
        if (!isLoggedIn()) {
            redirect('/member/login?redirect=/admin');
            exit;
        }
        
        if (!isAdmin()) {
            redirect('/');
            exit;
        }
    }
    
    /**
     * 관리자 대시보드
     */
    public function index() {
        // 통계 데이터 조회
        $stats = [
            'total_members' => getDbCnt("SELECT COUNT(*) FROM member"),
            'total_posts' => getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE status = 'active'"),
            'total_comments' => getDbCnt("SELECT COUNT(*) FROM bbs_comment WHERE status = 'active'"),
            'today_members' => getDbCnt("SELECT COUNT(*) FROM member WHERE DATE(reg_date) = CURDATE()"),
            'today_posts' => getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE DATE(created_at) = CURDATE()"),
            'today_comments' => getDbCnt("SELECT COUNT(*) FROM bbs_comment WHERE DATE(created_at) = CURDATE()")
        ];
        
        // 최근 회원 목록
        $recent_members = getDbArray("
            SELECT uid, user_id, name, email, level, reg_date 
            FROM member 
            ORDER BY reg_date DESC 
            LIMIT 5
        ");
        
        // 최근 게시물 목록
        $recent_posts = getDbArray("
            SELECT uid, board_id, subject, writer, views, created_at 
            FROM bbs_index 
            WHERE status = 'active'
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        $data = [
            'title' => '관리자 대시보드',
            'stats' => $stats,
            'recent_members' => $recent_members,
            'recent_posts' => $recent_posts
        ];
        
        $this->view('admin/dashboard', $data);
    }
    
    /**
     * 사이트 설정
     */
    public function config($action = '') {
        // 하위 메서드 처리
        if ($action === 'uploadLogo') {
            $this->uploadLogo();
            return;
        } elseif ($action === 'deleteLogo') {
            $this->deleteLogo();
            return;
        } elseif ($action === 'saveDimensions') {
            $this->saveDimensions();
            return;
        } elseif ($action === 'saveBasic') {
            $this->saveBasicConfig();
            return;
        } elseif ($action === 'saveImageSettings') {
            $this->saveImageSettings();
            return;
        } elseif ($action === 'saveWatermarkSettings') {
            $this->saveWatermarkSettings();
            return;
        } elseif ($action === 'uploadWatermark') {
            $this->uploadWatermark();
            return;
        } elseif ($action === 'deleteWatermark') {
            $this->deleteWatermark();
            return;
        }
        
        // 모든 설정 로드 (admin_config와 site_config 모두)
        $adminConfigRows = getDbArray("SELECT config_key, config_value FROM admin_config");
        $siteConfigRows = getDbArray("SELECT config_key, config_value FROM site_config");
        
        $configs = [];
        foreach ($adminConfigRows as $row) {
            $configs[$row['config_key']] = $row['config_value'];
        }
        foreach ($siteConfigRows as $row) {
            $configs[$row['config_key']] = $row['config_value'];
        }
        
        $data = [
            'title' => '사이트 설정',
            'configs' => $configs
        ];
        
        $this->view('admin/config', $data);
    }
    
    /**
     * 기본 설정 저장
     */
    private function saveBasicConfig() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $configs = [
            'site_name' => cleanInput($_POST['site_name'] ?? ''),
            'site_url' => cleanInput($_POST['site_url'] ?? ''),
            'site_email' => cleanInput($_POST['site_email'] ?? '')
        ];
        
        foreach ($configs as $key => $value) {
            setConfig($key, $value);
        }
        
        // var 파일로 저장
        $this->saveConfigToVar('site', $configs);
        
        $this->json(['success' => true, 'message' => '설정이 저장되었습니다.']);
    }
    
    /**
     * 로고 업로드
     */
    private function uploadLogo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => '파일 업로드 오류가 발생했습니다.'], 400);
            return;
        }
        
        $logoType = cleanInput($_POST['logo_type'] ?? '');
        $width = (int)($_POST['width'] ?? 0);
        $height = (int)($_POST['height'] ?? 0);
        
        $file = $_FILES['logo'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed)) {
            $this->json(['success' => false, 'message' => '이미지 파일만 업로드 가능합니다.'], 400);
            return;
        }
        
        // 업로드 디렉토리
        $uploadDir = __DIR__ . '/../../public/uploads/logos';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 파일명 생성
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $logoType . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $url = '/public/uploads/logos/' . $filename;
            
            // DB에 저장
            setConfig($logoType, $url);
            setConfig($logoType . '_width', $width);
            setConfig($logoType . '_height', $height);
            
            // var 파일 저장
            $logoSettings = getDbArray("SELECT config_key, config_value FROM admin_config WHERE config_key LIKE '%logo%'");
            $settings = [];
            foreach ($logoSettings as $setting) {
                $settings[$setting['config_key']] = $setting['config_value'];
            }
            $this->saveConfigToVar('logo', $settings);
            
            $this->json(['success' => true, 'url' => $url]);
        } else {
            $this->json(['success' => false, 'message' => '파일 저장에 실패했습니다.'], 500);
        }
    }
    
    /**
     * 로고 크기 저장
     */
    private function saveDimensions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false], 400);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $logoType = cleanInput($input['logo_type'] ?? '');
        $width = (int)($input['width'] ?? 0);
        $height = (int)($input['height'] ?? 0);
        
        if ($width > 0 && $height > 0) {
            setConfig($logoType . '_width', $width);
            setConfig($logoType . '_height', $height);
            
            // var 파일 저장
            $logoSettings = getDbArray("SELECT config_key, config_value FROM admin_config WHERE config_key LIKE '%logo%'");
            $settings = [];
            foreach ($logoSettings as $setting) {
                $settings[$setting['config_key']] = $setting['config_value'];
            }
            $this->saveConfigToVar('logo', $settings);
            
            $this->json(['success' => true]);
        } else {
            $this->json(['success' => false], 400);
        }
    }
    
    /**
     * 로고 삭제
     */
    private function deleteLogo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false], 400);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $logoType = cleanInput($input['logo_type'] ?? '');
        
        // 파일 경로 가져오기
        $logoUrl = getConfig($logoType);
        if ($logoUrl) {
            $filepath = __DIR__ . '/../../' . ltrim($logoUrl, '/');
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        // DB에서 삭제
        setConfig($logoType, '');
        setConfig($logoType . '_width', '');
        setConfig($logoType . '_height', '');
        
        $this->json(['success' => true]);
    }
    
    /**
     * 사이트 설정 저장
     */
    public function configSave() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        }
        
        // 설정 항목들
        $configs = [
            'site_name' => cleanInput($this->post('site_name')),
            'site_url' => cleanInput($this->post('site_url')),
            'site_email' => cleanInput($this->post('site_email')),
            'about_title' => cleanInput($this->post('about_title')),
            'about_content' => $this->post('about_content') // HTML 허용
        ];
        
        // 각 설정을 DB에 저장
        foreach ($configs as $key => $value) {
            // 설정이 이미 존재하는지 확인
            $existing = getUidData("SELECT uid FROM admin_config WHERE config_key = ?", [$key]);
            
            if ($existing) {
                // 업데이트
                getDbUpdate('admin_config', 
                    ['config_value' => $value], 
                    'config_key = ?', 
                    [$key]
                );
            } else {
                // 신규 삽입
                getDbInsert('admin_config', [
                    'config_key' => $key,
                    'config_value' => $value,
                    'config_group' => 'general'
                ]);
            }
        }
        
        $this->json([
            'success' => true,
            'message' => '설정이 저장되었습니다.'
        ]);
    }
    
    /**
     * 회원 관리
     */
    public function members($page = 1) {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // 검색 조건
        $search = cleanInput($this->get('search', ''));
        $status = cleanInput($this->get('status', ''));
        $level = cleanInput($this->get('level', ''));
        
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
        
        $this->view('admin/members', $data);
    }
    
    /**
     * 회원 상세/수정
     */
    public function member($uid, $action = '') {
        // 하위 액션 처리
        if ($action === 'update') {
            $this->memberUpdate();
            return;
        } elseif ($action === 'resetPassword') {
            $this->memberResetPassword();
            return;
        } elseif ($action === 'delete') {
            $this->memberDelete();
            return;
        }
        
        // 회원 정보 조회
        $member = getUidData("SELECT * FROM member WHERE uid = ?", [$uid]);
        
        if (!$member) {
            $this->redirect('/admin/members');
            return;
        }
        
        // 활동 통계
        $stats = [
            'post_count' => getUidData("SELECT COUNT(*) as cnt FROM bbs_index WHERE member_uid = ? AND status = 'active'", [$uid])['cnt'] ?? 0,
            'comment_count' => getUidData("SELECT COUNT(*) as cnt FROM bbs_comment WHERE member_uid = ? AND status = 'active'", [$uid])['cnt'] ?? 0
        ];
        
        // 최근 게시물
        $recentPosts = getDbArray("
            SELECT uid, board_id, title, created_at, view
            FROM bbs_index
            WHERE member_uid = ? AND status = 'active'
            ORDER BY created_at DESC
            LIMIT 10
        ", [$uid]);
        
        // 최근 댓글
        $recentComments = getDbArray("
            SELECT c.uid, c.parent_uid, c.content, c.created_at, i.board_id, i.title as post_title
            FROM bbs_comment c
            LEFT JOIN bbs_index i ON c.parent_uid = i.uid
            WHERE c.member_uid = ? AND c.status = 'active'
            ORDER BY c.created_at DESC
            LIMIT 10
        ", [$uid]);
        
        $data = [
            'title' => '회원 상세',
            'member' => $member,
            'stats' => $stats,
            'recent_posts' => $recentPosts,
            'recent_comments' => $recentComments
        ];
        
        $this->view('admin/member_detail', $data);
    }
    
    /**
     * 회원 정보 수정
     */
    private function memberUpdate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $uid = (int)$_POST['uid'];
        
        $updateData = [
            'name' => cleanInput($_POST['name'] ?? ''),
            'nickname' => cleanInput($_POST['nickname'] ?? ''),
            'email' => cleanInput($_POST['email'] ?? ''),
            'level' => (int)($_POST['level'] ?? 1),
            'point' => (int)($_POST['point'] ?? 0),
            'status' => cleanInput($_POST['status'] ?? 'active'),
            'phone' => cleanInput($_POST['phone'] ?? ''),
            'tel' => cleanInput($_POST['tel'] ?? ''),
            'address' => cleanInput($_POST['address'] ?? '')
        ];
        
        $result = getDbUpdate('member', $updateData, $uid);
        
        if ($result !== false) {
            $this->json(['success' => true, 'message' => '회원 정보가 수정되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '수정 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 비밀번호 재설정
     */
    private function memberResetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $uid = (int)$_POST['uid'];
        $newPassword = $_POST['new_password'] ?? '';
        
        if (strlen($newPassword) < 8) {
            $this->json(['success' => false, 'message' => '비밀번호는 8자 이상이어야 합니다.'], 400);
            return;
        }
        
        $hashedPassword = hashPassword($newPassword);
        $result = getDbUpdate('member', ['password' => $hashedPassword], $uid);
        
        if ($result !== false) {
            $this->json(['success' => true, 'message' => '비밀번호가 재설정되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '재설정 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 회원 삭제
     */
    private function memberDelete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $uid = (int)($input['uid'] ?? 0);
        
        // 회원을 탈퇴 상태로 변경
        $result = getDbUpdate('member', ['status' => 'withdrawn'], $uid);
        
        if ($result !== false) {
            $this->json(['success' => true, 'message' => '회원이 삭제(탈퇴처리)되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '삭제 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 게시판 관리
     */
    public function boards() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // FormData로 전송된 데이터 처리
            $boardData = [
                'board_id' => cleanInput($_POST['board_id']),
                'board_name' => cleanInput($_POST['board_name']),
                'board_skin' => cleanInput($_POST['board_skin'] ?? 'default'),
                'posts_per_page' => (int)($_POST['posts_per_page'] ?? 20),
                'read_level' => (int)($_POST['read_level'] ?? 1),
                'write_level' => (int)($_POST['write_level'] ?? 1),
                'comment_level' => (int)($_POST['comment_level'] ?? 1),
                'use_comment' => cleanInput($_POST['use_comment'] ?? 'Y'),
                'use_category' => cleanInput($_POST['use_category'] ?? 'N'),
                'status' => 'active'
            ];
            
            $result = getDbInsert('bbs_list', $boardData);
            
            if ($result) {
                $this->json([
                    'success' => true,
                    'message' => '게시판이 생성되었습니다.',
                    'board_id' => $result
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => '게시판 생성 중 오류가 발생했습니다.'
                ], 500);
            }
            return;
        }
        
        // 게시판 목록
        $boards = getDbArray("
            SELECT b.*, 
                   (SELECT COUNT(*) FROM bbs_index WHERE board_id = b.board_id AND status = 'active') as post_count
            FROM bbs_list b
            WHERE b.status != 'deleted'
            ORDER BY b.created_at DESC
        ");
        
        $data = [
            'title' => '게시판 관리',
            'boards' => $boards
        ];
        
        $this->view('admin/boards', $data);
    }
    
    /**
     * 게시판 수정
     */
    public function board($uid) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // JSON 요청 처리
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            $updateData = [
                'board_name' => cleanInput($data['board_name']),
                'board_skin' => cleanInput($data['board_skin']),
                'posts_per_page' => (int)$data['posts_per_page'],
                'read_level' => (int)$data['read_level'],
                'write_level' => (int)$data['write_level'],
                'comment_level' => (int)$data['comment_level'],
                'use_comment' => cleanInput($data['use_comment']),
                'use_category' => cleanInput($data['use_category']),
                'status' => cleanInput($data['status'])
            ];
            
            $result = getDbUpdate('bbs_list', $updateData, 'uid = ?', [$uid]);
            
            if ($result !== false) {
                $this->json([
                    'success' => true,
                    'message' => '게시판 설정이 수정되었습니다.'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => '수정 중 오류가 발생했습니다.'
                ], 500);
            }
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // 게시판 삭제
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            // 게시판의 모든 게시물도 삭제 (status 변경)
            getDbUpdate('bbs_index', ['status' => 'deleted'], 'board_id IN (SELECT board_id FROM bbs_list WHERE uid = ?)', [$uid]);
            
            // 게시판 삭제
            $result = getDbUpdate('bbs_list', ['status' => 'deleted'], 'uid = ?', [$uid]);
            
            if ($result !== false) {
                $this->json([
                    'success' => true,
                    'message' => '게시판이 삭제되었습니다.'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => '삭제 중 오류가 발생했습니다.'
                ], 500);
            }
            return;
        }
        
        $board = getUidData("SELECT * FROM bbs_list WHERE uid = ?", [$uid]);
        
        if (!$board) {
            redirect('/admin/boards');
        }
        
        $data = [
            'title' => '게시판 설정',
            'board' => $board
        ];
        
        $this->view('admin/board_detail', $data);
    }
    
    /**
     * 통계 페이지
     */
    public function statistics() {
        $period = $this->get('period', '30'); // 기본 30일
        
        // 기간 설정
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        $endDate = date('Y-m-d');
        
        // 오늘/어제 날짜
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthAgo = date('Y-m-d', strtotime('-30 days'));
        
        // 방문자 통계
        $visitorStats = [
            'today' => getDbCnt("SELECT COUNT(DISTINCT ip_address) FROM visitor_stats WHERE visit_date = ?", [$today]),
            'yesterday' => getDbCnt("SELECT COUNT(DISTINCT ip_address) FROM visitor_stats WHERE visit_date = ?", [$yesterday]),
            'week' => getDbCnt("SELECT COUNT(DISTINCT ip_address) FROM visitor_stats WHERE visit_date >= ?", [$weekAgo]),
            'month' => getDbCnt("SELECT COUNT(DISTINCT ip_address) FROM visitor_stats WHERE visit_date >= ?", [$monthAgo]),
            'total' => getDbCnt("SELECT COUNT(DISTINCT ip_address, visit_date) FROM visitor_stats")
        ];
        
        // 게시물 통계
        $postStats = [
            'today' => getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE DATE(created_at) = ? AND status = 'active'", [$today]),
            'yesterday' => getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE DATE(created_at) = ? AND status = 'active'", [$yesterday]),
            'week' => getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE DATE(created_at) >= ? AND status = 'active'", [$weekAgo]),
            'month' => getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE DATE(created_at) >= ? AND status = 'active'", [$monthAgo]),
            'total' => getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE status = 'active'")
        ];
        
        // 회원 가입 통계
        $memberStats = [
            'today' => getDbCnt("SELECT COUNT(*) FROM member WHERE DATE(reg_date) = ?", [$today]),
            'yesterday' => getDbCnt("SELECT COUNT(*) FROM member WHERE DATE(reg_date) = ?", [$yesterday]),
            'week' => getDbCnt("SELECT COUNT(*) FROM member WHERE DATE(reg_date) >= ?", [$weekAgo]),
            'month' => getDbCnt("SELECT COUNT(*) FROM member WHERE DATE(reg_date) >= ?", [$monthAgo]),
            'total' => getDbCnt("SELECT COUNT(*) FROM member")
        ];
        
        // KPI 통계
        $stats = [
            'total_visitors' => $visitorStats['total'],
            'new_members' => $memberStats['month'],
            'new_posts' => $postStats['month'],
            'active_users' => getDbCnt("
                SELECT COUNT(DISTINCT uid) 
                FROM member 
                WHERE last_login >= ?
            ", [date('Y-m-d', strtotime('-7 days'))])
        ];
        
        // 일별 방문자 추이
        $dailyVisits = getDbArray("
            SELECT visit_date as date, COUNT(DISTINCT ip_address) as count
            FROM visitor_stats
            WHERE visit_date BETWEEN ? AND ?
            GROUP BY visit_date
            ORDER BY visit_date ASC
        ", [$startDate, $endDate]);
        
        // 회원 가입 추이
        $dailySignups = getDbArray("
            SELECT DATE(reg_date) as date, COUNT(*) as count
            FROM member
            WHERE DATE(reg_date) BETWEEN ? AND ?
            GROUP BY DATE(reg_date)
            ORDER BY date ASC
        ", [$startDate, $endDate]);
        
        // 게시물 작성 추이
        $dailyPosts = getDbArray("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM bbs_index
            WHERE status = 'active' AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", [$startDate, $endDate]);
        
        // 게시판별 게시물 수
        $postsByBoard = getDbArray("
            SELECT b.board_name, COUNT(p.uid) as count
            FROM bbs_list b
            LEFT JOIN bbs_index p ON b.board_id = p.board_id AND p.status = 'active'
            WHERE b.status != 'deleted'
            GROUP BY b.uid, b.board_name
            ORDER BY count DESC
            LIMIT 10
        ");
        
        // 인기 게시물 TOP 10
        $topPosts = getDbArray("
            SELECT uid, board_id, subject, writer, views, comments, created_at
            FROM bbs_index
            WHERE status = 'active' AND DATE(created_at) BETWEEN ? AND ?
            ORDER BY views DESC
            LIMIT 10
        ", [$startDate, $endDate]);
        
        $data = [
            'title' => '통계',
            'period' => $period,
            'stats' => $stats,
            'visitorStats' => $visitorStats,
            'postStats' => $postStats,
            'memberStats' => $memberStats,
            'dailyVisits' => $dailyVisits,
            'dailySignups' => $dailySignups,
            'dailyPosts' => $dailyPosts,
            'postsByBoard' => $postsByBoard,
            'topPosts' => $topPosts
        ];
        
        $this->view('admin/statistics', $data);
    }
    
    /**
     * AJAX 통계 데이터
     */
    public function statisticsData() {
        $period = $this->get('period', '30');
        
        $startDate = date('Y-m-d', strtotime("-{$period} days"));
        $endDate = date('Y-m-d');
        
        // 일별 방문자 추이
        $dailyVisits = getDbArray("
            SELECT DATE(created_at) as date, COUNT(DISTINCT ip_address) as count
            FROM bbs_index
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", [$startDate, $endDate]);
        
        // 데이터 포맷 (Chart.js 형식)
        $this->json([
            'success' => true,
            'data' => [
                'labels' => array_column($dailyVisits, 'date'),
                'datasets' => [
                    [
                        'label' => '일별 방문자',
                        'data' => array_column($dailyVisits, 'count'),
                        'backgroundColor' => 'rgba(52, 152, 219, 0.2)',
                        'borderColor' => 'rgba(52, 152, 219, 1)',
                        'borderWidth' => 2
                    ]
                ]
            ]
        ]);
    }
    
    /**
     * 사이트 설정 - 파비콘
     */
    public function favicon() {
        $data = [
            'title' => '파비콘 설정',
            'favicon_url' => getConfig('favicon_url', '')
        ];
        $this->view('admin/favicon', $data);
    }
    
    /**
     * 사이트 설정 - 헤더 코드
     */
    public function headercode() {
        $data = [
            'title' => '헤더 코드',
            'header_code' => getConfig('header_code', '')
        ];
        $this->view('admin/headercode', $data);
    }
    
    /**
     * 사이트 설정 - 푸터 코드
     */
    public function footercode() {
        $data = [
            'title' => '푸터 코드',
            'footer_code' => getConfig('footer_code', '')
        ];
        $this->view('admin/footercode', $data);
    }
    
    /**
     * 사이트 설정 - RSS
     */
    public function rss() {
        $boards = getDbArray("SELECT board_id, board_name FROM bbs_list WHERE status = 'active' ORDER BY board_name");
        $data = [
            'title' => 'RSS 설정',
            'boards' => $boards,
            'rss_boards' => json_decode(getConfig('rss_boards', '[]'), true),
            'rss_exclude' => json_decode(getConfig('rss_exclude', '[]'), true),
            'rss_period' => getConfig('rss_period', '30')
        ];
        $this->view('admin/rss', $data);
    }
    
    /**
     * 사이트 설정 - 사이트맵
     */
    public function sitemap() {
        $boards = getDbArray("SELECT board_id, board_name FROM bbs_list WHERE status = 'active' ORDER BY board_name");
        $data = [
            'title' => '사이트맵 설정',
            'boards' => $boards,
            'sitemap_exclude' => json_decode(getConfig('sitemap_exclude', '[]'), true)
        ];
        $this->view('admin/sitemap', $data);
    }
    
    /**
     * 회원가입 설정
     */
    public function joinconfig($action = '') {
        // save 액션 처리
        if ($action === 'save') {
            $this->saveJoinConfig();
            return;
        }
        
        // GET 요청: 설정 페이지 표시
        // var 파일에서 설정 로드
        $varFile = __DIR__ . '/../config/var/member_join.var.php';
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
        $this->view('admin/joinconfig', $data);
    }
    
    /**
     * 회원가입 설정 저장
     */
    private function saveJoinConfig() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
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
            $varDir = __DIR__ . '/../config/var';
            if (!is_dir($varDir)) {
                mkdir($varDir, 0755, true);
            }
            
            $varFile = $varDir . '/member_join.var.php';
            $varContent = "<?php\n";
            $varContent .= "// 회원가입 설정\n";
            $varContent .= "// 자동 생성 파일 - 수동으로 수정하지 마세요\n\n";
            $varContent .= "\$join_config = " . var_export($config, true) . ";\n";
            
            if (file_put_contents($varFile, $varContent)) {
                $this->json(['success' => true, 'message' => '가입 환경 설정이 저장되었습니다.']);
            } else {
                $this->json(['success' => false, 'message' => '파일 저장에 실패했습니다.']);
            }
        } else {
            $this->json(['success' => false, 'message' => '잘못된 설정 유형입니다.'], 400);
        }
    }
    
    /**
     * 회원 등급 관리
     */
    public function levels() {
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
                $this->json(['success' => (bool)$result, 'message' => $result ? '등급이 추가되었습니다.' : '등급 추가 실패']);
                return;
            } elseif ($action === 'update') {
                $uid = (int)$_POST['uid'];
                $updateData = [
                    'level_name' => cleanInput($_POST['level_name']),
                    'point_min' => (int)($_POST['point_min'] ?? 0),
                    'point_max' => (int)($_POST['point_max'] ?? 0)
                ];
                $result = getDbUpdate('member_level', $updateData, $uid);
                $this->json(['success' => (bool)$result, 'message' => $result ? '등급이 수정되었습니다.' : '등급 수정 실패']);
                return;
            } elseif ($action === 'delete') {
                $uid = (int)$_POST['uid'];
                $result = getDbDelete('member_level', $uid);
                $this->json(['success' => (bool)$result, 'message' => $result ? '등급이 삭제되었습니다.' : '등급 삭제 실패']);
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
        $this->view('admin/levels', $data);
    }
    
    /**
     * 회원 포인트 지급
     */
    public function points() {
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
            
            $this->json(['success' => true, 'message' => '포인트가 지급되었습니다.']);
            return;
        }
        
        $members = getDbArray("SELECT uid, user_id, name, point FROM member WHERE status = 'active' ORDER BY user_id ASC");
        $data = [
            'title' => '회원 포인트 지급',
            'members' => $members
        ];
        $this->view('admin/points', $data);
    }
    
    /**
     * 게시물 리스트
     */
    public function posts() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        $boardId = $_GET['board_id'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (d.title LIKE ? OR d.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($boardId) {
            $where .= " AND i.bbs_id = ?";
            $params[] = $boardId;
        }
        
        $total = getDbCnt("
            SELECT COUNT(*) 
            FROM bbs_index i
            INNER JOIN bbs_data d ON i.data_uid = d.uid
            $where
        ", $params);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $posts = getDbArray("
            SELECT d.*, i.bbs_id, i.category, i.is_notice, i.is_secret
            FROM bbs_index i
            INNER JOIN bbs_data d ON i.data_uid = d.uid
            $where
            ORDER BY d.reg_date DESC 
            LIMIT $perPage OFFSET $offset
        ", $params);
        
        $boards = getDbArray("SELECT bbs_id, bbs_name FROM bbs_list ORDER BY bbs_name");
        
        $data = [
            'title' => '게시물 리스트',
            'posts' => $posts,
            'boards' => $boards,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search' => $search,
            'board_id' => $boardId
        ];
        
        $this->view('admin/posts', $data);
    }
    
    /**
     * 댓글 리스트
     */
    public function comments() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $search = $_GET['search'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (content LIKE ? OR writer LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $total = getDbCnt("SELECT COUNT(*) FROM bbs_comment $where", $params);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $comments = getDbArray("
            SELECT c.*, b.subject as post_subject, b.board_id
            FROM bbs_comment c
            LEFT JOIN bbs_index b ON c.post_uid = b.uid
            $where
            ORDER BY c.created_at DESC 
            LIMIT $perPage OFFSET $offset
        ", $params);
        
        $data = [
            'title' => '댓글 리스트',
            'comments' => $comments,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search' => $search
        ];
        
        $this->view('admin/comments', $data);
    }
    
    /**
     * 방문자 통계 (일별/월별)
     */
    public function visitor() {
        $type = $_GET['type'] ?? 'daily';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        if ($type === 'daily') {
            $stats = getDbArray("
                SELECT DATE(visit_date) as date, COUNT(DISTINCT ip_address) as count
                FROM visitor_stats
                WHERE visit_date BETWEEN ? AND ?
                GROUP BY DATE(visit_date)
                ORDER BY date ASC
            ", [$startDate, $endDate]);
        } else {
            $stats = getDbArray("
                SELECT DATE_FORMAT(visit_date, '%Y-%m') as month, COUNT(DISTINCT ip_address) as count
                FROM visitor_stats
                WHERE visit_date BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
                ORDER BY month ASC
            ", [$startDate, $endDate]);
        }
        
        $data = [
            'title' => '방문자 통계',
            'type' => $type,
            'stats' => $stats,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
        
        $this->view('admin/visitor', $data);
    }
    
    /**
     * 방문자 추적
     */
    public function tracking() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 50;
        $search = $_GET['search'] ?? '';
        
        $where = "WHERE 1=1";
        $params = [];
        
        if ($search) {
            $where .= " AND (ip_address LIKE ? OR page_url LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $total = getDbCnt("SELECT COUNT(*) FROM visitor_stats $where", $params);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $visitors = getDbArray("
            SELECT * FROM visitor_stats 
            $where
            ORDER BY created_at DESC 
            LIMIT $perPage OFFSET $offset
        ", $params);
        
        $data = [
            'title' => '방문자 추적',
            'visitors' => $visitors,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'search' => $search
        ];
        
        $this->view('admin/tracking', $data);
    }
    
    /**
     * 게시물 통계
     */
    public function poststats() {
        $type = $_GET['type'] ?? 'daily';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $boardFilter = $_GET['board_id'] ?? '';
        
        // 게시판 목록
        $boards = getDbArray("SELECT board_id, board_name FROM bbs_list WHERE status != 'deleted' ORDER BY board_name");
        
        // 통계 데이터
        if ($type === 'daily') {
            $stats = getDbArray("
                SELECT DATE(created_at) as date, COUNT(*) as count
                FROM bbs_index
                WHERE created_at BETWEEN ? AND ? AND status = 'active'
                GROUP BY DATE(created_at)
                ORDER BY date ASC
            ", [$startDate, $endDate]);
        } else {
            $stats = getDbArray("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
                FROM bbs_index
                WHERE created_at BETWEEN ? AND ? AND status = 'active'
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ", [$startDate, $endDate]);
        }
        
        // 게시판별 통계
        $boardStats = getDbArray("
            SELECT b.board_id, b.board_name, 
                   COUNT(i.uid) as post_count,
                   COALESCE(SUM(i.comment), 0) as comment_count,
                   COALESCE(SUM(i.view), 0) as total_views,
                   COALESCE(AVG(i.view), 0) as avg_views,
                   MAX(i.created_at) as last_post_date
            FROM bbs_list b
            LEFT JOIN bbs_index i ON b.board_id = i.board_id 
                AND i.created_at BETWEEN ? AND ? 
                AND i.status = 'active'
            WHERE b.status != 'deleted'
            GROUP BY b.board_id, b.board_name
            ORDER BY post_count DESC
        ", [$startDate, $endDate]);
        
        // 작성자별 통계 (TOP 10)
        $authorStats = getDbArray("
            SELECT m.uid, m.user_id, m.name,
                   COUNT(DISTINCT i.uid) as post_count,
                   COALESCE(SUM(i.comment), 0) as comment_count,
                   COALESCE(SUM(i.view), 0) as total_views,
                   MAX(i.created_at) as last_activity
            FROM member m
            LEFT JOIN bbs_index i ON m.uid = i.member_uid 
                AND i.created_at BETWEEN ? AND ? 
                AND i.status = 'active'
            WHERE m.status = 'active'
            GROUP BY m.uid, m.user_id, m.name
            ORDER BY post_count DESC
            LIMIT 10
        ", [$startDate, $endDate]);
        
        // 통계 카드용 데이터
        $totalPosts = getUidData("SELECT COUNT(*) as cnt FROM bbs_index WHERE status = 'active'", [])['cnt'] ?? 0;
        $todayPosts = getUidData("SELECT COUNT(*) as cnt FROM bbs_index WHERE DATE(created_at) = CURDATE() AND status = 'active'", [])['cnt'] ?? 0;
        $weekPosts = getUidData("SELECT COUNT(*) as cnt FROM bbs_index WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status = 'active'", [])['cnt'] ?? 0;
        $avgDaily = getUidData("SELECT COUNT(*) / DATEDIFF(MAX(created_at), MIN(created_at)) as avg FROM bbs_index WHERE status = 'active'", [])['avg'] ?? 0;
        
        $data = [
            'title' => '게시물 통계',
            'type' => $type,
            'stats' => $stats,
            'board_stats' => $boardStats,
            'author_stats' => $authorStats,
            'boards' => $boards,
            'board_filter' => $boardFilter,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_posts' => $totalPosts,
            'today_posts' => $todayPosts,
            'week_posts' => $weekPosts,
            'avg_daily' => round($avgDaily, 1)
        ];
        
        $this->view('admin/poststats', $data);
    }
    
    /**
     * 헤더 메뉴 관리
     */
    public function menu($action = 'header') {
        if ($action === 'header') {
            // 헤더 메뉴 목록 조회
            $menus = getDbArray("
                SELECT * FROM header_menu 
                ORDER BY menu_order ASC, id ASC
            ") ?? [];
            
            $data = [
                'title' => '헤더 메뉴 관리',
                'menus' => $menus
            ];
            
            $this->view('admin/menu_header', $data);
        } elseif ($action === 'footer') {
            // 푸터 메뉴 (추후 구현)
            $data = [
                'title' => '푸터 메뉴 관리'
            ];
            
            $this->view('admin/menu_footer', $data);
        }
    }
    
    /**
     * 헤더 메뉴 생성 (콤마로 여러 개 생성 가능)
     */
    public function createMenu() {
        // 디버그 로그
        error_log("createMenu called - REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . json_encode($_POST));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 400);
            return;
        }
        
        $menuNames = trim($_POST['menu_name'] ?? '');
        error_log("Menu names: " . $menuNames);
        
        if (empty($menuNames)) {
            $this->json(['success' => false, 'message' => '메뉴명을 입력해주세요.']);
            return;
        }
        
        // 콤마로 분리
        $names = array_map('trim', explode(',', $menuNames));
        $names = array_filter($names); // 빈 값 제거
        
        if (empty($names)) {
            $this->json(['success' => false, 'message' => '유효한 메뉴명이 없습니다.']);
            return;
        }
        
        try {
            // 현재 최대 순서 조회
            $maxOrderResult = getUidData("SELECT COALESCE(MAX(menu_order), 0) as max_order FROM header_menu", []);
            $maxOrder = $maxOrderResult['max_order'] ?? 0;
            error_log("Max order: " . $maxOrder);
            
            $successCount = 0;
            foreach ($names as $name) {
                $maxOrder++;
                error_log("Inserting menu: $name with order: $maxOrder");
                
                $result = getDbInsert('header_menu', [
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
                $this->json(['success' => true, 'message' => $message, 'count' => $successCount]);
            } else {
                $this->json(['success' => false, 'message' => '메뉴 생성에 실패했습니다.']);
            }
        } catch (Exception $e) {
            error_log("Error in createMenu: " . $e->getMessage());
            $this->json(['success' => false, 'message' => '오류: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 헤더 메뉴 삭제
     */
    public function deleteMenu($id = null) {
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid menu ID'], 400);
            return;
        }
        
        // 1. 메뉴 정보 조회 (타입 확인용)
        $menu = getUidData("SELECT * FROM header_menu WHERE id = ?", [$id]);
        
        if (!$menu) {
            $this->json(['success' => false, 'message' => '메뉴를 찾을 수 없습니다.'], 404);
            return;
        }
        
        // 2. 페이지 타입이면 관련 데이터 삭제
        if ($menu['menu_type'] === 'page') {
            // 2-1. 첨부파일 실제 파일 삭제 (menu_page_upload로 변경)
            $pageFiles = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ?", [$id]);
            foreach ($pageFiles as $file) {
                // filepath에서 실제 경로 추출
                $realFilePath = __DIR__ . '/../../' . ltrim($file['filepath'], '/');
                if (file_exists($realFilePath)) {
                    @unlink($realFilePath);
                    error_log("첨부파일 삭제: " . $realFilePath);
                }
            }
            // DB에서 첨부파일 정보 삭제
            getDbDelete('menu_page_upload', 'menu_id = ?', [$id]);
            
            // 2-2. DB에서 페이지 콘텐츠 삭제
            getDbDelete('menu_pages', 'menu_id = ?', [$id]);
            
            // 2-3. 페이지 PHP 파일 삭제
            $pageFilePath = __DIR__ . '/../../public/uploads/page/' . $id . '.php';
            if (file_exists($pageFilePath)) {
                if (unlink($pageFilePath)) {
                    error_log("페이지 파일 삭제 완료: " . $pageFilePath);
                } else {
                    error_log("페이지 파일 삭제 실패: " . $pageFilePath);
                }
            }
        }
        
        // 3. 서브메뉴 확인 및 삭제
        $subMenus = getDbArray("SELECT id, menu_type FROM header_menu WHERE parent_id = ?", [$id]);
        if (!empty($subMenus)) {
            foreach ($subMenus as $subMenu) {
                // 서브메뉴가 페이지 타입이면 파일도 삭제
                if ($subMenu['menu_type'] === 'page') {
                    // 서브메뉴 첨부파일 삭제 (menu_page_upload로 변경)
                    $subPageFiles = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ?", [$subMenu['id']]);
                    foreach ($subPageFiles as $file) {
                        $realFilePath = __DIR__ . '/../../' . ltrim($file['filepath'], '/');
                        if (file_exists($realFilePath)) {
                            @unlink($realFilePath);
                        }
                    }
                    getDbDelete('menu_page_upload', 'menu_id = ?', [$subMenu['id']]);
                    
                    getDbDelete('menu_pages', 'menu_id = ?', [$subMenu['id']]);
                    $subPageFilePath = __DIR__ . '/../../public/uploads/page/' . $subMenu['id'] . '.php';
                    if (file_exists($subPageFilePath)) {
                        @unlink($subPageFilePath);
                    }
                }
            }
            // 서브메뉴 삭제
            getDbDelete('header_menu', 'parent_id = ?', [$id]);
        }
        
        // 4. 메뉴 삭제
        $result = getDbDelete('header_menu', 'id = ?', [$id]);
        
        if ($result) {
            $this->json(['success' => true, 'message' => '메뉴가 삭제되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '메뉴 삭제에 실패했습니다.']);
        }
    }
    
    /**
     * 헤더 메뉴 순서 변경
     */
    public function updateMenuOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $orders = $this->post('orders', []);
        
        if (empty($orders)) {
            $this->json(['success' => false, 'message' => '순서 데이터가 없습니다.']);
            return;
        }
        
        // 순서 업데이트
        foreach ($orders as $order => $id) {
            getDbUpdate('header_menu', ['menu_order' => $order + 1], 'id = ?', [$id]);
        }
        
        $this->json(['success' => true, 'message' => '순서가 변경되었습니다.']);
    }
    
    /**
     * 메뉴 수정 페이지
     */
    public function editMenu($id = null) {
        if (!$id) {
            redirect('/admin/menu/header');
            return;
        }
        
        // 메뉴 정보 조회
        $menu = getUidData("SELECT * FROM header_menu WHERE id = ?", [$id]);
        
        if (!$menu) {
            redirect('/admin/menu/header');
            return;
        }
        
        // 게시판 목록 조회 (하드코딩된 게시판 타입)
        $boards = [
            ['board_id' => 'free', 'board_name' => '자유게시판'],
            ['board_id' => 'notice', 'board_name' => '공지사항'],
            ['board_id' => 'qna', 'board_name' => 'Q&A'],
            ['board_id' => 'gallery', 'board_name' => '갤러리'],
            ['board_id' => 'video', 'board_name' => '동영상'],
            ['board_id' => 'list', 'board_name' => '리스트형']
        ];
        
        // 페이지 콘텐츠 조회
        $pageContent = '';
        $pageFiles = [];
        if ($menu['menu_type'] === 'page') {
            $page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$id]);
            $pageContent = $page['content'] ?? '';
            
            // 첨부파일 목록 조회 (menu_page_upload로 변경)
            $pageFiles = getDbArray("SELECT * FROM menu_page_upload WHERE menu_id = ? ORDER BY uid ASC", [$id]);
        }
        
        $data = [
            'title' => '메뉴 수정',
            'menu' => $menu,
            'boards' => $boards,
            'pageContent' => $pageContent,
            'pageFiles' => $pageFiles
        ];
        
        $this->view('admin/menu_edit', $data);
    }
    
    /**
     * 메뉴 업데이트
     */
    public function updateMenu($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $menuName = trim($this->post('menu_name', ''));
        $menuType = $this->post('menu_type', 'page');
        $menuTarget = trim($this->post('menu_target', ''));
        $customUrl = trim($this->post('custom_url', ''));
        $useRedirect = $this->post('use_redirect', 'N');
        $targetWindow = $this->post('target_window', 'self');
        $isHidden = $this->post('is_hidden', 'N');
        $isBlocked = $this->post('is_blocked', 'N');
        
        if (empty($menuName)) {
            $this->json(['success' => false, 'message' => '메뉴명을 입력해주세요.']);
            return;
        }
        
        // 메뉴 업데이트
        $result = getDbUpdate('header_menu', [
            'menu_name' => $menuName,
            'menu_type' => $menuType,
            'menu_target' => $menuTarget,
            'custom_url' => $customUrl,
            'use_redirect' => $useRedirect,
            'target_window' => $targetWindow,
            'is_hidden' => $isHidden,
            'is_blocked' => $isBlocked
        ], 'id = ?', [$id]);
        
        // 페이지 타입이면 콘텐츠 저장
        if ($menuType === 'page') {
            $pageContent = $this->post('page_content', '');
            
            // 1. DB에 저장 (편집용 백업)
            $existingPage = getUidData("SELECT id FROM menu_pages WHERE menu_id = ?", [$id]);
            
            if ($existingPage) {
                getDbUpdate('menu_pages', ['content' => $pageContent], 'menu_id = ?', [$id]);
            } else {
                getDbInsert('menu_pages', [
                    'menu_id' => $id,
                    'menu_table' => 'header',
                    'content' => $pageContent
                ]);
            }
            
            // 2. 파일로 저장 (실행용)
            $pageFilePath = __DIR__ . '/../../public/uploads/page/' . $id . '.php';
            $pageFileContent = '<?php
/**
 * 메뉴 페이지: ' . $menuName . '
 * 메뉴 ID: ' . $id . '
 * 생성일: ' . date('Y-m-d H:i:s') . '
 * 
 * 이 파일은 자동 생성되었습니다.
 * 관리자 페이지에서 수정하세요: /admin/editMenu/' . $id . '
 */
?>
' . $pageContent;
            
            // 파일 저장
            if (file_put_contents($pageFilePath, $pageFileContent) === false) {
                error_log("페이지 파일 저장 실패: " . $pageFilePath);
            } else {
                @chmod($pageFilePath, 0644); // 읽기 권한 설정
            }
        }
        
        if ($result !== false) {
            $this->json(['success' => true, 'message' => '메뉴가 수정되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '메뉴 수정에 실패했습니다.']);
        }
    }
    
    /**
     * 페이지 첨부파일 삭제
     */
    public function deletePageFile($fileId = null) {
        if (!$fileId) {
            $this->json(['success' => false, 'message' => 'Invalid file ID'], 400);
            return;
        }
        
        // 파일 정보 조회
        $file = getUidData("SELECT * FROM menu_page_upload WHERE uid = ?", [$fileId]);
        
        if (!$file) {
            $this->json(['success' => false, 'message' => '파일을 찾을 수 없습니다.'], 404);
            return;
        }
        
        // 실제 파일 삭제
        $realFilePath = __DIR__ . '/../../' . ltrim($file['filepath'], '/');
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
            $this->json(['success' => true, 'message' => '파일이 삭제되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '파일 삭제에 실패했습니다.']);
        }
    }
    
    /**
     * 서브메뉴 추가
     */
    public function addSubmenu($parentId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$parentId) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
            return;
        }
        
        $menuName = trim($this->post('menu_name', ''));
        
        if (empty($menuName)) {
            $this->json(['success' => false, 'message' => '메뉴명을 입력해주세요.']);
            return;
        }
        
        // 부모 메뉴의 최대 순서 조회
        $maxOrder = getUidData("
            SELECT MAX(menu_order) as max_order 
            FROM header_menu 
            WHERE parent_id = ?
        ", [$parentId])['max_order'] ?? 0;
        
        // 서브메뉴 추가
        $result = getDbInsert('header_menu', [
            'parent_id' => $parentId,
            'menu_name' => $menuName,
            'menu_type' => 'page',
            'menu_order' => $maxOrder + 1,
            'is_active' => 'Y'
        ]);
        
        if ($result) {
            $this->json(['success' => true, 'message' => '서브메뉴가 추가되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '서브메뉴 추가에 실패했습니다.']);
        }
    }
    
    /**
     * 이미지 설정 저장
     */
    private function saveImageSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $settings = [
            'image_max_width' => (int)($_POST['image_max_width'] ?? 900),
            'image_quality' => (int)($_POST['image_quality'] ?? 100),
            'thumb_big_width' => (int)($_POST['thumb_big_width'] ?? 900),
            'thumb_big_height' => (int)($_POST['thumb_big_height'] ?? 600),
            'thumb_middle_width' => (int)($_POST['thumb_middle_width'] ?? 640),
            'thumb_middle_height' => (int)($_POST['thumb_middle_height'] ?? 480),
            'thumb_small_width' => (int)($_POST['thumb_small_width'] ?? 480),
            'thumb_small_height' => (int)($_POST['thumb_small_height'] ?? 360),
            'thumb_quality' => (int)($_POST['thumb_quality'] ?? 100),
            'thumbnail_delete_original' => cleanInput($_POST['thumbnail_delete_original'] ?? 'N'),
            'thumbnail_transparent_bg' => cleanInput($_POST['thumbnail_transparent_bg'] ?? 'white')
        ];
        
        // 유효성 검사
        if ($settings['image_quality'] < 1 || $settings['image_quality'] > 100) {
            $this->json(['success' => false, 'message' => '이미지 품질은 1~100 사이여야 합니다.'], 400);
            return;
        }
        
        if ($settings['thumb_quality'] < 1 || $settings['thumb_quality'] > 100) {
            $this->json(['success' => false, 'message' => '썸네일 해상도는 1~100 사이여야 합니다.'], 400);
            return;
        }
        
        if (!in_array($settings['thumbnail_delete_original'], ['Y', 'N'])) {
            $settings['thumbnail_delete_original'] = 'N';
        }
        
        if (!in_array($settings['thumbnail_transparent_bg'], ['white', 'black'])) {
            $settings['thumbnail_transparent_bg'] = 'white';
        }
        
        // site_config 테이블에 저장
        foreach ($settings as $key => $value) {
            getDbUpdate('site_config', ['config_value' => $value], 'config_key = ?', [$key]);
        }
        
        // var 파일로 저장
        $this->saveConfigToVar('image', $settings);
        
        $this->json(['success' => true, 'message' => '이미지 설정이 저장되었습니다.']);
    }
    
    /**
     * 워터마크 설정 저장
     */
    private function saveWatermarkSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $settings = [
            'watermark_enabled' => cleanInput($_POST['watermark_enabled'] ?? 'N'),
            'watermark_target_board' => cleanInput($_POST['watermark_target_board'] ?? 'Y'),
            'watermark_target_page' => cleanInput($_POST['watermark_target_page'] ?? 'Y'),
            'watermark_position' => (int)($_POST['watermark_position'] ?? 5),
            'watermark_opacity' => (int)($_POST['watermark_opacity'] ?? 80)
        ];
        
        // 유효성 검사
        if (!in_array($settings['watermark_enabled'], ['Y', 'N'])) {
            $settings['watermark_enabled'] = 'N';
        }
        
        if (!in_array($settings['watermark_target_board'], ['Y', 'N'])) {
            $settings['watermark_target_board'] = 'Y';
        }
        
        if (!in_array($settings['watermark_target_page'], ['Y', 'N'])) {
            $settings['watermark_target_page'] = 'Y';
        }
        
        if ($settings['watermark_position'] < 1 || $settings['watermark_position'] > 5) {
            $settings['watermark_position'] = 5;
        }
        
        if ($settings['watermark_opacity'] < 0 || $settings['watermark_opacity'] > 100) {
            $settings['watermark_opacity'] = 80;
        }
        
        // site_config 테이블에 저장
        foreach ($settings as $key => $value) {
            getDbUpdate('site_config', ['config_value' => $value], 'config_key = ?', [$key]);
        }
        
        $this->json(['success' => true, 'message' => '워터마크 설정이 저장되었습니다.']);
    }
    
    /**
     * 워터마크 이미지 업로드
     */
    private function uploadWatermark() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        if (!isset($_FILES['watermark'])) {
            $this->json(['success' => false, 'message' => '파일이 전송되지 않았습니다.'], 400);
            return;
        }
        
        if ($_FILES['watermark']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = '파일 업로드 오류: ';
            switch ($_FILES['watermark']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $errorMsg .= 'php.ini의 upload_max_filesize를 초과했습니다.';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $errorMsg .= 'HTML 폼의 MAX_FILE_SIZE를 초과했습니다.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errorMsg .= '파일이 부분적으로만 업로드되었습니다.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errorMsg .= '파일이 업로드되지 않았습니다.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $errorMsg .= '임시 폴더가 없습니다.';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $errorMsg .= '디스크 쓰기에 실패했습니다.';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $errorMsg .= 'PHP 확장이 업로드를 중단했습니다.';
                    break;
                default:
                    $errorMsg .= '알 수 없는 오류 (' . $_FILES['watermark']['error'] . ')';
            }
            $this->json(['success' => false, 'message' => $errorMsg], 400);
            return;
        }
        
        $file = $_FILES['watermark'];
        
        // MIME 타입 체크 (finfo 사용)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        // PNG 파일만 허용
        if ($mimeType !== 'image/png') {
            $this->json(['success' => false, 'message' => 'PNG 파일만 업로드 가능합니다. (업로드된 파일 타입: ' . $mimeType . ')'], 400);
            return;
        }
        
        // 파일 크기 체크 (2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->json(['success' => false, 'message' => '파일 크기는 2MB를 초과할 수 없습니다.'], 400);
            return;
        }
        
        // 업로드 디렉토리
        $uploadDir = __DIR__ . '/../../public/uploads/watermark';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $this->json(['success' => false, 'message' => '업로드 디렉토리 생성 실패'], 500);
                return;
            }
        }
        
        // 디렉토리 쓰기 권한 체크
        if (!is_writable($uploadDir)) {
            $this->json(['success' => false, 'message' => '업로드 디렉토리 쓰기 권한 없음'], 500);
            return;
        }
        
        // 기존 워터마크 삭제
        $oldWatermark = getUidData("SELECT config_value FROM site_config WHERE config_key = 'watermark_image'", [])['config_value'] ?? '';
        if (!empty($oldWatermark) && file_exists(__DIR__ . '/../../public' . $oldWatermark)) {
            @unlink(__DIR__ . '/../../public' . $oldWatermark);
        }
        
        // 파일명 생성
        $filename = 'watermark_' . time() . '.png';
        $filepath = $uploadDir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            @chmod($filepath, 0644);
            $url = '/uploads/watermark/' . $filename;
            
            // DB에 저장
            getDbUpdate('site_config', ['config_value' => $url], 'config_key = ?', ['watermark_image']);
            
            // var 파일 업데이트 (현재 워터마크 설정 전체 읽어서 저장)
            $watermarkSettings = getDbArray("SELECT config_key, config_value FROM site_config WHERE config_key LIKE 'watermark%'");
            $settings = [];
            foreach ($watermarkSettings as $setting) {
                $settings[$setting['config_key']] = $setting['config_value'];
            }
            $this->saveConfigToVar('watermark', $settings);
            
            $this->json(['success' => true, 'url' => $url]);
        } else {
            $this->json(['success' => false, 'message' => '파일 업로드에 실패했습니다. 경로: ' . $filepath], 500);
        }
    }
    
    /**
     * 워터마크 삭제
     */
    private function deleteWatermark() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        // DB에서 워터마크 경로 조회
        $watermark = getUidData("SELECT config_value FROM site_config WHERE config_key = 'watermark_image'", [])['config_value'] ?? '';
        
        if (!empty($watermark)) {
            $filepath = __DIR__ . '/../../public' . $watermark;
            if (file_exists($filepath)) {
                @unlink($filepath);
            }
            
            // DB에서 삭제
            getDbUpdate('site_config', ['config_value' => ''], 'config_key = ?', ['watermark_image']);
            
            // var 파일 업데이트
            $watermarkSettings = getDbArray("SELECT config_key, config_value FROM site_config WHERE config_key LIKE 'watermark%'");
            $settings = [];
            foreach ($watermarkSettings as $setting) {
                $settings[$setting['config_key']] = $setting['config_value'];
            }
            $this->saveConfigToVar('watermark', $settings);
        }
        
        $this->json(['success' => true, 'message' => '워터마크가 삭제되었습니다.']);
    }
    
    /**
     * 설정을 var 파일로 저장
     */
    private function saveConfigToVar($filename, $settings) {
        $varDir = __DIR__ . '/../config/var';
        
        // 디렉토리가 없으면 생성
        if (!is_dir($varDir)) {
            mkdir($varDir, 0755, true);
        }
        
        $varFile = $varDir . '/' . $filename . '.var.php';
        
        $content = "<?php\n";
        $content .= "/**\n";
        $content .= " * " . $filename . " 설정 파일\n";
        $content .= " * 자동 생성됨: " . date('Y-m-d H:i:s') . "\n";
        $content .= " */\n\n";
        $content .= "return [\n";
        
        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                $content .= "    '{$key}' => '" . addslashes($value) . "',\n";
            } elseif (is_int($value)) {
                $content .= "    '{$key}' => {$value},\n";
            } elseif (is_bool($value)) {
                $content .= "    '{$key}' => " . ($value ? 'true' : 'false') . ",\n";
            } else {
                $content .= "    '{$key}' => null,\n";
            }
        }
        
        $content .= "];\n";
        
        // 파일 쓰기
        $result = file_put_contents($varFile, $content);
        
        if ($result !== false) {
            @chmod($varFile, 0644);
            return true;
        }
        
        return false;
    }
}

