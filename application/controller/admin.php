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
        }
        
        // 모든 설정 로드
        $configRows = getDbArray("SELECT config_key, config_value FROM admin_config");
        $configs = [];
        foreach ($configRows as $row) {
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
            $where .= " AND (subject LIKE ? OR writer LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($boardId) {
            $where .= " AND board_id = ?";
            $params[] = $boardId;
        }
        
        $total = getDbCnt("SELECT COUNT(*) FROM bbs_index $where", $params);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        
        $posts = getDbArray("
            SELECT * FROM bbs_index 
            $where
            ORDER BY created_at DESC 
            LIMIT $perPage OFFSET $offset
        ", $params);
        
        $boards = getDbArray("SELECT board_id, board_name FROM bbs_list WHERE status = 'active' ORDER BY board_name");
        
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
                
                $result = setDbData("
                    INSERT INTO header_menu (parent_id, menu_name, menu_type, menu_order, is_active)
                    VALUES (0, ?, 'page', ?, 'Y')
                ", [$name, $maxOrder]);
                
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
        
        $result = setDbData("DELETE FROM header_menu WHERE id = ?", [$id]);
        
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
            setDbData("UPDATE header_menu SET menu_order = ? WHERE id = ?", [$order + 1, $id]);
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
        
        // 게시판 목록 조회
        $boards = getDbArray("SELECT bbs_id, bbs_name FROM bbs_boards ORDER BY bbs_name ASC") ?? [];
        
        // 페이지 콘텐츠 조회
        $pageContent = '';
        if ($menu['menu_type'] === 'page') {
            $page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$id]);
            $pageContent = $page['content'] ?? '';
        }
        
        $data = [
            'title' => '메뉴 수정',
            'menu' => $menu,
            'boards' => $boards,
            'pageContent' => $pageContent
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
        $result = setDbData("
            UPDATE header_menu 
            SET menu_name = ?, menu_type = ?, menu_target = ?, custom_url = ?, 
                use_redirect = ?, target_window = ?, is_hidden = ?, is_blocked = ?
            WHERE id = ?
        ", [$menuName, $menuType, $menuTarget, $customUrl, $useRedirect, $targetWindow, $isHidden, $isBlocked, $id]);
        
        // 페이지 타입이면 콘텐츠 저장
        if ($menuType === 'page') {
            $pageContent = $this->post('page_content', '');
            
            // 기존 페이지 확인
            $existingPage = getUidData("SELECT id FROM menu_pages WHERE menu_id = ?", [$id]);
            
            if ($existingPage) {
                setDbData("UPDATE menu_pages SET content = ? WHERE menu_id = ?", [$pageContent, $id]);
            } else {
                setDbData("INSERT INTO menu_pages (menu_id, content) VALUES (?, ?)", [$id, $pageContent]);
            }
        }
        
        if ($result !== false) {
            $this->json(['success' => true, 'message' => '메뉴가 수정되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '메뉴 수정에 실패했습니다.']);
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
        $result = setDbData("
            INSERT INTO header_menu (parent_id, menu_name, menu_type, menu_order, is_active)
            VALUES (?, ?, 'page', ?, 'Y')
        ", [$parentId, $menuName, $maxOrder + 1]);
        
        if ($result) {
            $this->json(['success' => true, 'message' => '서브메뉴가 추가되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '서브메뉴 추가에 실패했습니다.']);
        }
    }
}

