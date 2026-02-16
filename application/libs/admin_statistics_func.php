<?php
/**
 * Admin Statistics Functions
 * 관리자 통계 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 통계 메인 페이지
 */
function admin_statistics_handler($controller) {
    $period = $controller->getParam('period', '30'); // 기본 30일
    
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
    
    $controller->renderView('admin/statistics', $data);
}

/**
 * AJAX 통계 데이터
 */
function admin_statistics_data_handler($controller) {
    $type = cleanInput($_GET['type'] ?? 'visitor');
    $period = cleanInput($_GET['period'] ?? '7');
    
    $data = [];
    
    if ($type === 'visitor') {
        $data = getDbArray("
            SELECT DATE(visit_date) as date, COUNT(*) as count 
            FROM visitor 
            WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(visit_date)
            ORDER BY date ASC
        ", [(int)$period]);
    } elseif ($type === 'member') {
        $data = getDbArray("
            SELECT DATE(reg_date) as date, COUNT(*) as count 
            FROM member 
            WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(reg_date)
            ORDER BY date ASC
        ", [(int)$period]);
    } elseif ($type === 'post') {
        $data = getDbArray("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM bbs_index 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", [(int)$period]);
    }
    
    $controller->renderJson(['success' => true, 'data' => $data]);
}
