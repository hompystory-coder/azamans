<?php
/**
 * Admin Index (Dashboard) Functions
 * 관리자 대시보드 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 관리자 대시보드 핸들러
 * @param object $controller Admin controller instance
 */
function admin_index_handler($controller) {
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
    
    $controller->renderView('admin/dashboard', $data);
}
