<?php
/**
 * Admin Visitor Management Functions
 * 관리자 방문자 통계 및 추적 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 방문자 통계 (일별/월별)
 */
function admin_visitor_handler($controller) {
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
    
    $controller->renderView('admin/visitor', $data);
}

/**
 * 방문자 추적 (실시간 방문자 로그)
 */
function admin_tracking_handler($controller) {
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
    
    $controller->renderView('admin/tracking', $data);
}
