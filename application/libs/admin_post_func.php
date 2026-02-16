<?php
/**
 * Admin Post/Comment Management Functions
 * 관리자 게시물/댓글 관리 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 게시물 목록 조회
 */
function admin_posts_handler($controller) {
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
    
    $controller->renderView('admin/posts', $data);
}

/**
 * 게시물 삭제
 */
function admin_post_delete_handler($controller, $uid) {
    // JSON 요청만 허용
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    // 관리자 권한 확인
    if (!isAdmin()) {
        $controller->renderJson(['success' => false, 'message' => '권한이 없습니다.'], 403);
        return;
    }
    
    // BoardModel 로드
    require_once APP_PATH . '/models/BoardModel.php';
    $boardModel = new BoardModel();
    
    // 게시물 정보 조회
    $post = $boardModel->getPost($uid);
    
    if (!$post) {
        $controller->renderJson(['success' => false, 'message' => '존재하지 않는 게시물입니다.'], 404);
        return;
    }
    
    // 게시물 삭제
    $result = $boardModel->deletePost($uid);
    
    if ($result) {
        // 최적화 데이터 삭제
        bbsDeleteOptimizationData($post['bbs_id'], $uid);
        
        $controller->renderJson([
            'success' => true,
            'message' => '게시물이 삭제되었습니다.'
        ]);
    } else {
        $controller->renderJson([
            'success' => false,
            'message' => '삭제 중 오류가 발생했습니다.'
        ], 500);
    }
}

/**
 * 댓글 목록 조회
 */
function admin_comments_handler($controller) {
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
    
    $controller->renderView('admin/comments', $data);
}

/**
 * 댓글 삭제
 */
function admin_comment_delete_handler($controller, $uid) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $deleted = getDbUpdate('bbs_comment', ['status' => 'deleted'], 'uid = ?', [$uid]);
    
    if ($deleted) {
        $controller->renderJson(['success' => true, 'message' => '댓글이 삭제되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '삭제에 실패했습니다.'], 500);
    }
}

/**
 * 게시물 통계
 */
function admin_poststats_handler($controller) {
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
    
    $controller->renderView('admin/poststats', $data);
}
