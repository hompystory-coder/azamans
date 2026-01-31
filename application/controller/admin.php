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
            SELECT user_id, name, email, level, reg_date 
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 설정 저장
            $configs = [
                'site_name' => cleanInput($this->post('site_name')),
                'site_url' => cleanInput($this->post('site_url')),
                'site_email' => cleanInput($this->post('site_email')),
                'site_description' => cleanInput($this->post('site_description')),
                'posts_per_page' => (int)$this->post('posts_per_page', 20),
                'use_captcha' => $this->post('use_captcha', 'N')
            ];
            
            foreach ($configs as $key => $value) {
                setConfig($key, $value);
            }
            
            $this->json([
                'success' => true,
                'message' => '설정이 저장되었습니다.'
            ]);
        }
        
        // 현재 설정 조회
        $configs = getDbArray("SELECT config_key, config_value, description FROM admin_config");
        
        $data = [
            'title' => '사이트 설정',
            'configs' => $configs
        ];
        
        $this->view('admin/config', $data);
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
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // 전체 개수
        $totalMembers = getDbCnt("SELECT COUNT(*) FROM member {$whereClause}", $params);
        $totalPages = ceil($totalMembers / $perPage);
        
        // 회원 목록
        $members = getDbArray("
            SELECT uid, user_id, name, email, level, status, last_login, reg_date, post_count, comment_count
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
            'status' => $status
        ];
        
        $this->view('admin/members', $data);
    }
    
    /**
     * 회원 상세/수정
     */
    public function member($uid) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 회원 정보 수정
            $updateData = [
                'name' => cleanInput($this->post('name')),
                'email' => cleanInput($this->post('email')),
                'level' => (int)$this->post('level'),
                'status' => cleanInput($this->post('status'))
            ];
            
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
            // 게시판 생성
            $boardData = [
                'board_id' => cleanInput($this->post('board_id')),
                'board_name' => cleanInput($this->post('board_name')),
                'board_skin' => cleanInput($this->post('board_skin', 'default')),
                'posts_per_page' => (int)$this->post('posts_per_page', 20),
                'read_level' => (int)$this->post('read_level', 1),
                'write_level' => (int)$this->post('write_level', 1),
                'comment_level' => (int)$this->post('comment_level', 1),
                'use_comment' => cleanInput($this->post('use_comment', 'Y')),
                'use_category' => cleanInput($this->post('use_category', 'N')),
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
        }
        
        // 게시판 목록
        $boards = getDbArray("
            SELECT b.*, 
                   (SELECT COUNT(*) FROM bbs_index WHERE board_id = b.board_id AND status = 'active') as post_count
            FROM bbs_list b
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
            $updateData = [
                'board_name' => cleanInput($this->post('board_name')),
                'board_skin' => cleanInput($this->post('board_skin')),
                'posts_per_page' => (int)$this->post('posts_per_page'),
                'read_level' => (int)$this->post('read_level'),
                'write_level' => (int)$this->post('write_level'),
                'comment_level' => (int)$this->post('comment_level'),
                'use_comment' => cleanInput($this->post('use_comment')),
                'use_category' => cleanInput($this->post('use_category')),
                'status' => cleanInput($this->post('status'))
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
}
