<?php
/**
 * BBS Controller
 * 게시판 컨트롤러
 */

class Bbs extends Controller {
    
    private $boardModel;
    
    public function __construct() {
        $this->boardModel = $this->model('BoardModel');
    }
    
    /**
     * 게시판 라우팅
     * URL: /bbs/{boardId} 또는 /bbs/{boardId}/{action}/{param}
     */
    public function index($boardId = null, $action = null, $param = null) {
        // 디버깅: 모든 파라미터 로깅
        error_log("BBS Route Debug - boardId: " . var_export($boardId, true) . ", action: " . var_export($action, true) . ", param: " . var_export($param, true));
        error_log("BBS Route Debug - func_num_args: " . func_num_args());
        for ($i = 0; $i < func_num_args(); $i++) {
            error_log("BBS Route Debug - arg[$i]: " . var_export(func_get_arg($i), true));
        }
        
        // boardId가 없으면 홈으로
        if (!$boardId) {
            redirect('/');
        }
        
        // action에 따라 분기
        if ($action === 'view' && $param) {
            return $this->viewPost($boardId, $param);
        } elseif ($action === 'write' || $action === 'write-process') {
            if ($param === 'process' || $action === 'write-process') {
                return $this->writeProcess($boardId);
            }
            return $this->write($boardId);
        } elseif ($action === 'edit' || $action === 'edit-process') {
            if ($action === 'edit-process') {
                return $this->editProcess($boardId, $param);
            }
            $subAction = func_num_args() > 3 ? func_get_arg(3) : null;
            if ($subAction === 'process') {
                return $this->editProcess($boardId, $param);
            }
            return $this->edit($boardId, $param);
        } elseif ($action === 'delete' && $param) {
            return $this->delete($boardId, $param);
        } elseif ($action === 'like' && $param) {
            return $this->toggleLike($boardId, $param);
        } elseif ($action === 'download' && $param) {
            return $this->download($param);
        } elseif ($action === 'comment' && is_numeric($param)) {
            // /bbs/{boardId}/comment/{postUid} (신규 형식)
            $postUid = $param;
            $commentUid = func_num_args() > 3 ? func_get_arg(3) : null;
            $commentAction = func_num_args() > 4 ? func_get_arg(4) : null;
            
            if ($commentUid && $commentAction === 'delete') {
                return $this->commentDelete($boardId, $postUid, $commentUid);
            } elseif ($commentUid && $commentAction === 'edit') {
                return $this->commentEdit($boardId, $postUid, $commentUid);
            } else {
                return $this->commentWrite($boardId, $postUid);
            }
        } elseif (is_numeric($action) && $param === 'comment') {
            // /bbs/{boardId}/{postUid}/comment[/{commentUid}/{action}]
            $postUid = $action;
            $commentUid = func_num_args() > 3 ? func_get_arg(3) : null;
            $commentAction = func_num_args() > 4 ? func_get_arg(4) : null;
            
            error_log("Comment Route Detected - postUid: $postUid, commentUid: $commentUid, commentAction: $commentAction");
            
            if ($commentUid && $commentAction === 'delete') {
                // /bbs/{boardId}/{postUid}/comment/{commentUid}/delete
                error_log("Calling commentDelete($boardId, $postUid, $commentUid)");
                return $this->commentDelete($boardId, $postUid, $commentUid);
            } elseif ($commentUid && $commentAction === 'edit') {
                // /bbs/{boardId}/{postUid}/comment/{commentUid}/edit
                return $this->commentEdit($boardId, $postUid, $commentUid);
            } else {
                // /bbs/{boardId}/{postUid}/comment (작성)
                return $this->commentWrite($boardId, $postUid);
            }
        } elseif (is_numeric($action) && $param === 'comments') {
            // /bbs/{boardId}/{postUid}/comments - 댓글 목록 조회 (구 형식)
            $postUid = $action;
            return $this->getComments($boardId, $postUid);
        } elseif ($action === 'comments' && is_numeric($param)) {
            // /bbs/{boardId}/comments/{postUid} - 댓글 목록 조회 (신규 형식)
            $postUid = $param;
            return $this->getComments($boardId, $postUid);
        }
        
        // 기본: 게시판 목록
        $page = is_numeric($action) ? $action : 1;
        return $this->listPosts($boardId, $page);
    }
    
    /**
     * 게시판 목록
     */
    private function listPosts($boardId, $page = 1) {
        // 게시판 정보
        $board = $this->boardModel->getBoardInfo($boardId);
        
        if (!$board) {
            redirect('/');
        }
        
        // 권한 체크
        if ($board['read_level'] > ($_SESSION['level'] ?? 0)) {
            redirect('/member/login?redirect=/bbs/' . $boardId);
        }
        
        // 검색 파라미터
        $category = $this->get('category');
        $search = $this->get('search');
        
        // 게시물 목록
        $perPage = $board['page_rows'];
        $result = $this->boardModel->getPostList($boardId, $page, $perPage, $category, $search);
        
        $data = [
            'title' => $board['bbs_name'],
            'board' => $board,
            'notices' => $result['notices'],
            'posts' => $result['posts'],
            'total' => $result['total'],
            'current_page' => (int)$page,
            'total_pages' => $result['pages'],
            'category' => $category,
            'search' => $search
        ];
        
        $this->view('bbs/' . $board['bbs_skin'] . '/list', $data);
    }
    
    /**
     * 게시물 상세보기
     * URL: /bbs/{boardId}/view/{uid}
     */
    public function viewPost($boardId, $uid) {
        // 게시판 정보
        $board = $this->boardModel->getBoardInfo($boardId);
        
        if (!$board) {
            redirect('/');
        }
        
        // 권한 체크
        if ($board['read_level'] > ($_SESSION['level'] ?? 0)) {
            redirect('/member/login?redirect=/bbs/' . $boardId . '/view/' . $uid);
        }
        
        // 게시물 조회
        $post = $this->boardModel->getPost($uid);
        
        if (!$post || $post['bbs_id'] !== $boardId) {
            redirect('/bbs/' . $boardId);
        }
        
        // 댓글 조회
        $comments = $this->boardModel->getComments($uid);
        
        // 이전/다음 글
        $prevNext = $this->boardModel->getPrevNext($boardId, $uid);
        
        // 첨부파일 조회
        $files = $this->boardModel->getPostFiles($uid);
        
        // 현재 사용자의 좋아요 상태 확인
        $userLiked = false;
        if (isLoggedIn()) {
            $like = getUidData(
                "SELECT uid FROM post_likes WHERE post_uid = ? AND member_uid = ?",
                [$uid, $_SESSION['user_id']]
            );
            $userLiked = !empty($like);
        }
        
        $data = [
            'title' => $post['title'],
            'board' => $board,
            'post' => $post,
            'comments' => $comments,
            'prev' => $prevNext['prev'],
            'next' => $prevNext['next'],
            'files' => $files,
            'user_liked' => $userLiked
        ];
        
        $this->view('bbs/' . $board['bbs_skin'] . '/view', $data);
    }
    
    /**
     * 글쓰기 폼
     */
    public function write($boardId) {
        // 게시판 정보
        $board = $this->boardModel->getBoardInfo($boardId);
        
        if (!$board) {
            redirect('/');
        }
        
        // 권한 체크
        if ($board['write_level'] > ($_SESSION['level'] ?? 0)) {
            redirect('/member/login?redirect=/bbs/' . $boardId . '/write');
        }
        
        $data = [
            'title' => '글쓰기 - ' . $board['bbs_name'],
            'board' => $board,
            'mode' => 'write'
        ];
        
        $this->view('bbs/' . $board['bbs_skin'] . '/write', $data);
    }
    
    /**
     * 글쓰기 처리
     */
    public function writeProcess($boardId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/bbs/' . $boardId);
        }
        
        // 게시판 정보
        $board = $this->boardModel->getBoardInfo($boardId);
        
        if (!$board) {
            $this->json(['success' => false, 'message' => '존재하지 않는 게시판입니다.'], 404);
        }
        
        // 권한 체크
        if ($board['write_level'] > ($_SESSION['level'] ?? 0)) {
            $this->json(['success' => false, 'message' => '글쓰기 권한이 없습니다.'], 403);
        }
        
        // 입력 데이터
        $subject = cleanInput($this->post('subject'));
        $content = cleanInput($this->post('content'));
        $category = cleanInput($this->post('category'));
        
        // 유효성 검사
        if (empty($subject) || empty($content)) {
            $this->json(['success' => false, 'message' => '제목과 내용을 입력해주세요.'], 400);
        }
        
        // 게시물 데이터
        $postData = [
            'bbs_id' => $boardId,
            'title' => $subject,
            'content' => $content,
            'category' => $category,
            'name' => $_SESSION['nickname'] ?? ($this->post('writer') ?: '비회원'),
            'member_uid' => $_SESSION['user_id'] ?? null,
            'is_notice' => (isAdmin() && $this->post('is_notice') === 'Y') ? 'Y' : 'N',
            'is_secret' => ($this->post('use_secret') === 'Y') ? 'Y' : 'N'
        ];
        
        // 비회원 비밀번호
        if (!isLoggedIn()) {
            $writer = $this->post('writer');
            $password = $this->post('password');
            
            if (empty($writer)) {
                $this->json(['success' => false, 'message' => '작성자명을 입력해주세요.'], 400);
            }
            if (empty($password)) {
                $this->json(['success' => false, 'message' => '비밀번호를 입력해주세요.'], 400);
            }
            $postData['password'] = $password;
        }
        
        // 게시물 작성
        $postId = $this->boardModel->createPost($postData);
        
        if (!$postId) {
            $this->json(['success' => false, 'message' => '게시물 등록 중 오류가 발생했습니다.'], 500);
        }
        
        // 파일 업로드 처리
        if ($board['use_upload'] === 'Y' && !empty($_FILES['files']['name'][0])) {
            require_once __DIR__ . '/../libs/FileUpload.php';
            $uploader = new FileUpload();
            $uploadedFiles = $uploader->upload($_FILES['files']);
            
            if (!empty($uploadedFiles)) {
                $this->boardModel->attachFiles($postId, $uploadedFiles, $boardId);
            }
        }
        
        $this->json([
            'success' => true,
            'message' => '게시물이 등록되었습니다.',
            'redirect' => '/bbs/' . $boardId . '/view/' . $postId
        ]);
    }
    
    /**
     * 글수정 폼
     */
    public function edit($boardId, $uid) {
        // 게시판 정보
        $board = $this->boardModel->getBoardInfo($boardId);
        
        if (!$board) {
            redirect('/');
        }
        
        // 게시물 조회
        $post = $this->boardModel->getPost($uid);
        
        if (!$post || $post['bbs_id'] !== $boardId) {
            redirect('/bbs/' . $boardId);
        }
        
        // 수정 권한 체크 (작성자 또는 관리자)
        $canEdit = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($post['member_uid'] == $_SESSION['user_id'] || isAdmin()) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            redirect('/bbs/' . $boardId . '/view/' . $uid);
        }
        
        // 첨부 파일 목록
        $files = $this->boardModel->getPostFiles($uid);
        
        $data = [
            'title' => '글수정 - ' . $board['bbs_name'],
            'board' => $board,
            'post' => $post,
            'files' => $files,
            'mode' => 'edit'
        ];
        
        $this->view('bbs/' . $board['bbs_skin'] . '/write', $data);
    }
    
    /**
     * 글수정 처리
     */
    public function editProcess($boardId, $uid) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/bbs/' . $boardId);
        }
        
        // 게시판 정보
        $board = $this->boardModel->getBoardInfo($boardId);
        
        // 게시물 조회
        $post = $this->boardModel->getPost($uid);
        
        if (!$post || $post['bbs_id'] !== $boardId) {
            $this->json(['success' => false, 'message' => '존재하지 않는 게시물입니다.'], 404);
        }
        
        // 수정 권한 체크
        $canEdit = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($post['member_uid'] == $_SESSION['user_id'] || isAdmin()) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            $this->json(['success' => false, 'message' => '수정 권한이 없습니다.'], 403);
        }
        
        // 입력 데이터
        $updateData = [
            'title' => cleanInput($this->post('subject')),
            'content' => cleanInput($this->post('content')),
            'category' => cleanInput($this->post('category'))
        ];
        
        // 관리자인 경우 공지사항 설정 가능
        if (isAdmin()) {
            $updateData['is_notice'] = ($this->post('is_notice') === 'Y') ? 'Y' : 'N';
        }
        
        // 수정 처리
        $result = $this->boardModel->updatePost($uid, $updateData);
        
        if ($result === false) {
            $this->json(['success' => false, 'message' => '수정 중 오류가 발생했습니다.'], 500);
        }
        
        // 기존 파일 삭제 처리
        $deleteFiles = $this->post('delete_files');
        if (!empty($deleteFiles)) {
            $this->boardModel->deleteFiles($uid, $deleteFiles);
        }
        
        // 파일 업로드 처리
        if ($board['use_upload'] === 'Y' && !empty($_FILES['files']['name'][0])) {
            require_once __DIR__ . '/../libs/FileUpload.php';
            $uploader = new FileUpload();
            $uploadedFiles = $uploader->upload($_FILES['files']);
            
            if (!empty($uploadedFiles)) {
                $this->boardModel->attachFiles($uid, $uploadedFiles, $boardId);
            }
        }
        
        $this->json([
            'success' => true,
            'message' => '게시물이 수정되었습니다.',
            'redirect' => '/bbs/' . $boardId . '/view/' . $uid
        ]);
    }
    
    /**
     * 글삭제
     */
    public function delete($boardId, $uid) {
        // 게시물 조회
        $post = $this->boardModel->getPost($uid);
        
        if (!$post || $post['bbs_id'] !== $boardId) {
            $this->json(['success' => false, 'message' => '존재하지 않는 게시물입니다.'], 404);
        }
        
        // 삭제 권한 체크
        $canDelete = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($post['member_uid'] == $_SESSION['user_id'] || isAdmin()) {
                $canDelete = true;
            }
        }
        
        if (!$canDelete) {
            $this->json(['success' => false, 'message' => '삭제 권한이 없습니다.'], 403);
        }
        
        // 삭제 처리
        $result = $this->boardModel->deletePost($uid);
        
        if ($result) {
            $this->json([
                'success' => true,
                'message' => '게시물이 삭제되었습니다.',
                'redirect' => '/bbs/' . $boardId
            ]);
        } else {
            $this->json(['success' => false, 'message' => '삭제 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 작성
     */
    public function commentWrite($boardId, $postUid) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        }
        
        // 게시판 정보
        $board = $this->boardModel->getBoardInfo($boardId);
        
        if (!$board) {
            $this->json(['success' => false, 'message' => '존재하지 않는 게시판입니다.'], 400);
        }
        
        // 권한 체크
        if ($board['comment_level'] > ($_SESSION['level'] ?? 0)) {
            $this->json(['success' => false, 'message' => '댓글 작성 권한이 없습니다.'], 403);
        }
        
        $content = cleanInput($this->post('content'));
        
        if (empty($content)) {
            $this->json(['success' => false, 'message' => '댓글 내용을 입력해주세요.'], 400);
        }
        
        // 세션 정보 확인 (디버깅)
        error_log("Session Info - user_id: " . ($_SESSION['user_id'] ?? 'not set') . 
                  ", nickname: " . ($_SESSION['nickname'] ?? 'not set') . 
                  ", is_admin: " . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'true' : 'false') : 'not set'));
        
        // 작성자 이름 결정 (우선순위: nickname > name > username > user_id > '비회원')
        $writerName = '비회원';
        if (isLoggedIn()) {
            $writerName = $_SESSION['nickname'] ?? 
                         $_SESSION['name'] ?? 
                         $_SESSION['username'] ?? 
                         ($_SESSION['user_id'] ? 'user_' . $_SESSION['user_id'] : '비회원');
        }
        
        $commentData = [
            'bbs_id' => $boardId,
            'data_uid' => $postUid,
            'content' => $content,
            'name' => $writerName,
            'member_uid' => $_SESSION['user_id'] ?? 0,
            'ip_address' => getClientIP()
        ];
        
        $commentId = $this->boardModel->createComment($commentData);
        
        if ($commentId) {
            $this->json(['success' => true, 'message' => '댓글이 등록되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '댓글 등록 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 댓글 삭제
     */
    public function commentDelete($boardId, $postUid, $commentUid) {
        error_log("댓글 삭제 시작 - boardId: $boardId, postUid: $postUid, commentUid: $commentUid");
        error_log("세션 정보 - user_id: " . ($_SESSION['user_id'] ?? 'not set') . ", is_admin: " . (isAdmin() ? 'true' : 'false'));
        
        // 댓글 조회
        $comment = getUidData("SELECT * FROM bbs_comment WHERE uid = ?", [$commentUid]);
        
        if (!$comment) {
            error_log("댓글 삭제 실패 - 댓글을 찾을 수 없음: $commentUid");
            $this->json(['success' => false, 'message' => '존재하지 않는 댓글입니다.'], 404);
        }
        
        error_log("댓글 정보 - member_uid: " . $comment['member_uid']);
        
        // 삭제 권한 체크
        $canDelete = false;
        
        // 관리자는 모든 댓글 삭제 가능
        if (isAdmin()) {
            $canDelete = true;
            error_log("관리자 권한으로 삭제 허용");
        } 
        // 로그인한 사용자는 본인 댓글만 삭제 가능
        elseif (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($comment['member_uid'] == $_SESSION['user_id']) {
                $canDelete = true;
                error_log("본인 댓글 삭제 허용");
            }
        }
        // 비회원 댓글은 비밀번호 확인 필요 (추후 구현)
        
        if (!$canDelete) {
            error_log("댓글 삭제 권한 없음");
            $this->json(['success' => false, 'message' => '삭제 권한이 없습니다.'], 403);
        }
        
        $result = $this->boardModel->deleteComment($commentUid, $postUid);
        
        error_log("댓글 삭제 결과: " . ($result ? 'success' : 'failed'));
        
        if ($result) {
            $this->json(['success' => true, 'message' => '댓글이 삭제되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '삭제 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 파일 다운로드
     */
    public function download($fileUid) {
        $file = getUidData("SELECT * FROM bbs_data WHERE uid = ?", [$fileUid]);
        
        if (!$file) {
            echo '파일을 찾을 수 없습니다.';
            exit;
        }
        
        $filePath = PUBLIC_PATH . $file['file_path'];
        
        if (!file_exists($filePath)) {
            echo '파일이 존재하지 않습니다.';
            exit;
        }
        
        // 다운로드 횟수 증가
        $this->boardModel->incrementDownload($fileUid);
        
        // 파일 다운로드 헤더
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($filePath);
        exit;
    }
    
    /**
     * 댓글 목록 조회 (AJAX)
     */
    public function getComments($boardId, $postUid) {
        $comments = $this->boardModel->getComments($postUid);
        
        // 수정/삭제 권한 추가
        foreach ($comments as &$comment) {
            $canEdit = false;
            if (isLoggedIn() && isset($_SESSION['user_id'])) {
                if ($comment['member_uid'] == $_SESSION['user_id'] || isAdmin()) {
                    $canEdit = true;
                }
            }
            $comment['can_edit'] = $canEdit;
            $comment['reg_date'] = date('Y-m-d H:i', strtotime($comment['reg_date']));
        }
        
        $this->json([
            'success' => true,
            'comments' => $comments,
            'count' => count($comments)
        ]);
    }
    
    /**
     * 댓글 수정 (AJAX)
     */
    public function commentEdit($boardId, $postUid, $commentUid) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        }
        
        // 댓글 조회
        $comment = getUidData("SELECT * FROM bbs_comment WHERE uid = ?", [$commentUid]);
        
        if (!$comment) {
            $this->json(['success' => false, 'message' => '존재하지 않는 댓글입니다.'], 404);
        }
        
        // 수정 권한 체크
        $canEdit = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($comment['member_uid'] == $_SESSION['user_id'] || isAdmin()) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            $this->json(['success' => false, 'message' => '수정 권한이 없습니다.'], 403);
        }
        
        $content = cleanInput($this->post('content'));
        
        if (empty($content)) {
            $this->json(['success' => false, 'message' => '댓글 내용을 입력해주세요.'], 400);
        }
        
        // 댓글 수정
        $result = getDbUpdate('bbs_comment', ['content' => $content], 'uid = ?', [$commentUid]);
        
        if ($result !== false) {
            $this->json(['success' => true, 'message' => '댓글이 수정되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '댓글 수정 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 좋아요 토글 (AJAX)
     */
    public function toggleLike($boardId, $postUid) {
        // 로그인 확인
        if (!isLoggedIn()) {
            $this->json(['success' => false, 'message' => '로그인이 필요합니다.'], 401);
        }
        
        $memberUid = $_SESSION['user_id'];
        
        // 이미 좋아요를 눌렀는지 확인
        $existingLike = getUidData(
            "SELECT * FROM post_likes WHERE post_uid = ? AND member_uid = ?",
            [$postUid, $memberUid]
        );
        
        if ($existingLike) {
            // 좋아요 취소
            getDbDelete('post_likes', 'uid = ?', [$existingLike['uid']]);
            
            // bbs_data의 like_count 감소
            $post = getUidData("SELECT like_count FROM bbs_data WHERE uid = ?", [$postUid]);
            $newCount = max(0, ($post['like_count'] ?? 1) - 1);
            getDbUpdate('bbs_data', ['like_count' => $newCount], 'uid = ?', [$postUid]);
            
            $liked = false;
            $message = '좋아요를 취소했습니다.';
        } else {
            // 좋아요 추가
            getDbInsert('post_likes', [
                'post_uid' => $postUid,
                'member_uid' => $memberUid
            ]);
            
            // bbs_data의 like_count 증가
            $post = getUidData("SELECT like_count FROM bbs_data WHERE uid = ?", [$postUid]);
            $newCount = ($post['like_count'] ?? 0) + 1;
            getDbUpdate('bbs_data', ['like_count' => $newCount], 'uid = ?', [$postUid]);
            
            $liked = true;
            $message = '좋아요를 눌렀습니다.';
        }
        
        // 현재 좋아요 수 조회
        $post = getUidData("SELECT like_count FROM bbs_data WHERE uid = ?", [$postUid]);
        $likeCount = $post['like_count'] ?? 0;
        
        $this->json([
            'success' => true,
            'message' => $message,
            'liked' => $liked,
            'like_count' => $likeCount
        ]);
    }
}
