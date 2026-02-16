<?php
/**
 * Admin Board Functions
 * 관리자 게시판 관리 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 게시판 목록
 */
function admin_boards_handler($controller) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // FormData로 전송된 데이터 처리
            $boardData = [
                'bbs_id' => cleanInput($_POST['board_id']),
                'bbs_name' => cleanInput($_POST['board_name']),
                'bbs_skin' => cleanInput($_POST['board_skin'] ?? 'default'),
                'page_rows' => (int)($_POST['posts_per_page'] ?? 20),
                'read_level' => (int)($_POST['read_level'] ?? 1),
                'write_level' => (int)($_POST['write_level'] ?? 1),
                'comment_level' => (int)($_POST['comment_level'] ?? 1),
                'use_upload' => cleanInput($_POST['use_upload'] ?? 'Y'),
                'use_attach' => cleanInput($_POST['use_attach'] ?? 'Y'),
                'use_secret' => cleanInput($_POST['use_secret'] ?? 'Y'),
                'use_notice' => cleanInput($_POST['use_notice'] ?? 'Y')
            ];
            
            $result = getDbInsert('bbs_list', $boardData);
            
            if ($result) {
                $controller->renderJson([
                    'success' => true,
                    'message' => '게시판이 생성되었습니다.',
                    'board_id' => $result
                ]);
            } else {
                $controller->renderJson([
                    'success' => false,
                    'message' => '게시판 생성 중 오류가 발생했습니다.'
                ], 500);
            }
        } catch (Exception $e) {
            $controller->renderJson([
                'success' => false,
                'message' => '에러: ' . $e->getMessage()
            ], 500);
        }
        return;
    }
    
    // 게시판 목록
    $boards = getDbArray("
        SELECT b.*, 
               b.bbs_id as board_id,
               b.bbs_name as board_name,
               b.bbs_skin as board_skin,
               'active' as status,
               (SELECT COUNT(*) FROM bbs_index WHERE board_id = b.bbs_id) as post_count
        FROM bbs_list b
        ORDER BY b.reg_date DESC
    ");
    
    $data = [
        'title' => '게시판 관리',
        'boards' => $boards
    ];
    
    $controller->renderView('admin/boards', $data);
}

/**
 * 게시판 상세
 */
function admin_board_detail_handler($controller, $uid) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // JSON 요청 처리
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            
            // 디버깅 로그
            error_log("Board Update Request - UID: $uid, Data: " . json_encode($data));
            
            if (!$data) {
                throw new Exception('Invalid JSON data received');
            }
            
            $updateData = [
                'bbs_name' => cleanInput($data['board_name'] ?? ''),
                'bbs_skin' => cleanInput($data['board_skin'] ?? 'default'),
                'page_rows' => (int)($data['posts_per_page'] ?? 20),
                'read_level' => (int)($data['read_level'] ?? 1),
                'write_level' => (int)($data['write_level'] ?? 1),
                'comment_level' => (int)($data['comment_level'] ?? 1),
                'use_upload' => cleanInput($data['use_comment'] ?? 'Y'),
                'use_attach' => cleanInput($data['use_category'] ?? 'Y')
            ];
            
            // 선택적 필드들 (폼에 있을 경우에만 추가)
            if (isset($data['use_secret'])) {
                $updateData['use_secret'] = cleanInput($data['use_secret']);
            }
            if (isset($data['use_notice'])) {
                $updateData['use_notice'] = cleanInput($data['use_notice']);
            }
            
            error_log("Board Update Data: " . json_encode($updateData));
            
            $result = getDbUpdate('bbs_list', $updateData, 'uid = ?', [$uid]);
            
            error_log("Board Update Result: " . var_export($result, true));
            
            if ($result !== false) {
                $controller->renderJson([
                    'success' => true,
                    'message' => '게시판 설정이 수정되었습니다.'
                ]);
            } else {
                $controller->renderJson([
                    'success' => false,
                    'message' => '수정 중 오류가 발생했습니다.'
                ], 500);
            }
        } catch (Exception $e) {
            error_log("Board Update Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            $controller->renderJson([
                'success' => false,
                'message' => '에러: ' . $e->getMessage()
            ], 500);
        }
        return;
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            // 게시판 삭제
            $result = getDbDelete('bbs_list', 'uid = ?', [$uid]);
            
            if ($result) {
                $controller->renderJson([
                    'success' => true,
                    'message' => '게시판이 삭제되었습니다.'
                ]);
            } else {
                $controller->renderJson([
                    'success' => false,
                    'message' => '삭제 중 오류가 발생했습니다.'
                ], 500);
            }
        } catch (Exception $e) {
            $controller->renderJson([
                'success' => false,
                'message' => '에러: ' . $e->getMessage()
            ], 500);
        }
        return;
    }
    
    $board = getUidData("
        SELECT *,
               bbs_id as board_id,
               bbs_name as board_name,
               bbs_skin as board_skin,
               page_rows as posts_per_page,
               use_upload as use_comment,
               use_attach as use_category,
               'active' as status
        FROM bbs_list 
        WHERE uid = ?
    ", [$uid]);
    
    if (!$board) {
        redirect('/admin/boards');
    }
    
    $data = [
        'title' => '게시판 설정',
        'board' => $board
    ];
    
    $controller->renderView('admin/board_detail', $data);
}
