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
    public function config() {
        $data = [
            'title' => '사이트 설정'
        ];
        
        $this->view('admin/config', $data);
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
    public function member($uid) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // JSON 요청 처리
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            // 회원 정보 수정
            $updateData = [
                'name' => cleanInput($data['name']),
                'email' => cleanInput($data['email']),
                'level' => (int)$data['level'],
                'point' => (int)($data['point'] ?? 0),
                'status' => cleanInput($data['status'])
            ];
            
            // 비밀번호 변경이 있으면
            if (!empty($data['new_password'])) {
                $updateData['password'] = hashPassword($data['new_password']);
            }
            
            $result = getDbUpdate('member', $updateData, 'uid = ?', [$uid]);
            
            if ($result !== false) {
                $this->json([
                    'success' => true,
                    'message' => '회원 정보가 수정되었습니다.'
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
            // 회원 삭제
            $result = getDbUpdate('member', ['status' => 'withdrawn'], 'uid = ?', [$uid]);
            
            if ($result !== false) {
                $this->json([
                    'success' => true,
                    'message' => '회원이 삭제(탈퇴처리)되었습니다.'
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => '삭제 중 오류가 발생했습니다.'
                ], 500);
            }
            return;
        }
        
        // 회원 정보 조회
        $member = getUidData("SELECT * FROM member WHERE uid = ?", [$uid]);
        
        if (!$member) {
            redirect('/admin/members');
        }
        
        $data = [
            'title' => '회원 상세',
            'member' => $member
        ];
        
        $this->view('admin/member_detail', $data);
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
    public function joinconfig() {
        $data = [
            'title' => '회원가입 설정',
            'terms_of_service' => getConfig('terms_of_service', ''),
            'privacy_policy' => getConfig('privacy_policy', ''),
            'youth_protection' => getConfig('youth_protection', '')
        ];
        $this->view('admin/join_config', $data);
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
        
        $levels = getDbArray("SELECT * FROM member_level ORDER BY level ASC");
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
}
