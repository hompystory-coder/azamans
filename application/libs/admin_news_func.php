<?php
/**
 * Admin News Functions
 * 관리자 뉴스 관리 기능
 * 
 * 향후 확장 예정:
 * - 뉴스 카테고리 관리
 * - 뉴스 태그 시스템
 * - RSS 피드 연동
 * - 뉴스 발행 스케줄링
 * - 뉴스 통계 및 분석
 * - 외부 뉴스 API 연동
 * - 자동 번역 기능
 * - 뉴스 알림 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 뉴스 목록 관리
 */
function admin_news_list_handler($adminController) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newsData = [
            'news_id' => cleanInput($_POST['news_id']),
            'news_name' => cleanInput($_POST['news_name']),
            'news_skin' => cleanInput($_POST['news_skin'] ?? 'default'),
            'page_rows' => (int)($_POST['page_rows'] ?? 20),
            'read_level' => (int)($_POST['read_level'] ?? 1),
            'write_level' => (int)($_POST['write_level'] ?? 1),
            'comment_level' => (int)($_POST['comment_level'] ?? 1),
            'use_upload' => cleanInput($_POST['use_upload'] ?? 'Y'),
            'use_attach' => cleanInput($_POST['use_attach'] ?? 'Y'),
            'use_secret' => cleanInput($_POST['use_secret'] ?? 'Y'),
            'use_notice' => cleanInput($_POST['use_notice'] ?? 'Y')
        ];
        
        $result = getDbInsert('news_list', $newsData);
        
        if ($result) {
            $adminController->renderJson([
                'success' => true,
                'message' => '뉴스가 생성되었습니다.',
                'news_id' => $result
            ]);
        } else {
            $adminController->renderJson([
                'success' => false,
                'message' => '뉴스 생성 중 오류가 발생했습니다.'
            ], 500);
        }
        return;
    }
    
    // 뉴스 목록
    $newsList = getDbArray("
        SELECT n.*, 
               n.news_id as news_id,
               n.news_name as news_name,
               n.news_skin as news_skin,
               n.page_rows as posts_per_page,
               'active' as status,
               (SELECT COUNT(*) FROM news_data WHERE news_id = n.news_id) as post_count
        FROM news_list n
        ORDER BY n.reg_date DESC
    ") ?? [];
    
    $data = [
        'title' => '뉴스 관리',
        'newsList' => $newsList
    ];
    
    $adminController->renderView('admin/news_list', $data);
}

/**
 * 뉴스 상세 설정
 */
function admin_news_detail_handler($adminController, $uid) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // JSON 요청 처리
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            if (!$data) {
                throw new Exception('Invalid JSON data received');
            }
            
            $updateData = [
                'news_name' => cleanInput($data['news_name'] ?? ''),
                'news_skin' => cleanInput($data['news_skin'] ?? 'default'),
                'page_rows' => (int)($data['posts_per_page'] ?? 20),
                'read_level' => (int)($data['read_level'] ?? 1),
                'write_level' => (int)($data['write_level'] ?? 1),
                'comment_level' => (int)($data['comment_level'] ?? 1),
                'use_upload' => cleanInput($data['use_comment'] ?? 'Y'),
                'use_attach' => cleanInput($data['use_category'] ?? 'Y')
            ];
            
            // 선택적 필드들
            if (isset($data['use_secret'])) {
                $updateData['use_secret'] = cleanInput($data['use_secret']);
            }
            if (isset($data['use_notice'])) {
                $updateData['use_notice'] = cleanInput($data['use_notice']);
            }
            
            $result = getDbUpdate('news_list', $updateData, 'uid = ?', [$uid]);
            
            if ($result !== false) {
                $adminController->renderJson([
                    'success' => true,
                    'message' => '뉴스 설정이 수정되었습니다.'
                ]);
            } else {
                $adminController->renderJson([
                    'success' => false,
                    'message' => '수정 중 오류가 발생했습니다.'
                ], 500);
            }
        } catch (Exception $e) {
            $adminController->renderJson([
                'success' => false,
                'message' => '에러: ' . $e->getMessage()
            ], 500);
        }
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            // 뉴스 삭제
            $result = getDbDelete('news_list', 'uid = ?', [$uid]);
            
            if ($result) {
                $adminController->renderJson([
                    'success' => true,
                    'message' => '뉴스가 삭제되었습니다.'
                ]);
            } else {
                $adminController->renderJson([
                    'success' => false,
                    'message' => '삭제 중 오류가 발생했습니다.'
                ], 500);
            }
        } catch (Exception $e) {
            $adminController->renderJson([
                'success' => false,
                'message' => '에러: ' . $e->getMessage()
            ], 500);
        }
        return;
    }
    
    $news = getUidData("
        SELECT *,
               news_id as news_id,
               news_name as news_name,
               news_skin as news_skin,
               page_rows as posts_per_page,
               use_upload as use_comment,
               use_attach as use_category,
               'active' as status
        FROM news_list 
        WHERE uid = ?
    ", [$uid]);
    
    if (!$news) {
        redirect('/admin/news');
    }
    
    $data = [
        'title' => '뉴스 설정',
        'news' => $news
    ];
    
    $adminController->renderView('admin/news_detail', $data);
}

/**
 * 뉴스 포스트 관리
 */
function admin_news_posts_handler($adminController) {
    $page = $adminController->getParam('page', 1);
    $newsId = $adminController->getParam('news', '');
    $search = $adminController->getParam('search', '');
    
    $where = "1=1";
    $params = [];
    
    if ($newsId) {
        $where .= " AND n.news_id = ?";
        $params[] = $newsId;
    }
    
    if ($search) {
        $where .= " AND (n.title LIKE ? OR n.content LIKE ? OR n.name LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $posts = getDbArray("
        SELECT n.*, nl.news_name
        FROM news_data n
        LEFT JOIN news_list nl ON n.news_id = nl.news_id
        WHERE {$where}
        ORDER BY n.reg_date DESC
        LIMIT {$limit} OFFSET {$offset}
    ", $params) ?? [];
    
    $totalCount = getDbCnt("
        SELECT COUNT(*) 
        FROM news_data n
        WHERE {$where}
    ", $params);
    
    $totalPages = ceil($totalCount / $limit);
    
    $newsList = getDbArray("SELECT * FROM news_list ORDER BY news_name ASC") ?? [];
    
    $data = [
        'title' => '뉴스 리스트',
        'posts' => $posts,
        'newsList' => $newsList,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalCount' => $totalCount,
        'selectedNews' => $newsId,
        'search' => $search
    ];
    
    $adminController->renderView('admin/newsposts', $data);
}

/**
 * 뉴스 댓글 관리
 */
function admin_news_comments_handler($adminController) {
    $page = $adminController->getParam('page', 1);
    $newsId = $adminController->getParam('news', '');
    $search = $adminController->getParam('search', '');
    
    $where = "1=1";
    $params = [];
    
    if ($newsId) {
        $where .= " AND c.news_id = ?";
        $params[] = $newsId;
    }
    
    if ($search) {
        $where .= " AND (c.content LIKE ? OR c.name LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $comments = getDbArray("
        SELECT c.*, n.title as post_title, nl.news_name
        FROM news_comment c
        LEFT JOIN news_data n ON c.data_uid = n.uid
        LEFT JOIN news_list nl ON c.news_id = nl.news_id
        WHERE {$where}
        ORDER BY c.reg_date DESC
        LIMIT {$limit} OFFSET {$offset}
    ", $params) ?? [];
    
    $totalCount = getDbCnt("
        SELECT COUNT(*) 
        FROM news_comment c
        WHERE {$where}
    ", $params);
    
    $totalPages = ceil($totalCount / $limit);
    
    $newsList = getDbArray("SELECT * FROM news_list ORDER BY news_name ASC") ?? [];
    
    $data = [
        'title' => '뉴스 댓글 관리',
        'comments' => $comments,
        'newsList' => $newsList,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'totalCount' => $totalCount,
        'selectedNews' => $newsId,
        'search' => $search
    ];
    
    $adminController->renderView('admin/newscomments', $data);
}
