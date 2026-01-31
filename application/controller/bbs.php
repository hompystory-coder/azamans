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
     * 게시판 목록
     */
    public function index($boardId, $page = 1) {
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
        $perPage = $board['posts_per_page'];
        $result = $this->boardModel->getPostList($boardId, $page, $perPage, $category, $search);
        
        $data = [
            'title' => $board['board_name'],
            'board' => $board,
            'notices' => $result['notices'],
            'posts' => $result['posts'],
            'total' => $result['total'],
            'current_page' => (int)$page,
            'total_pages' => $result['pages'],
            'category' => $category,
            'search' => $search
        ];
        
        $this->view('bbs/' . $board['board_skin'] . '/list', $data);
    }
    
    /**
     * 게시물 상세보기
     */
    public function view($boardId, $uid) {
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
        
        if (!$post || $post['board_id'] !== $boardId) {
            redirect('/bbs/' . $boardId);
        }
        
        // 댓글 조회
        $comments = $this->boardModel->getComments($uid);
        
        // 이전/다음 글
        $prevNext = $this->boardModel->getPrevNext($boardId, $uid);
        
        // 첨부파일 조회
        $files = $this->boardModel->getPostFiles($uid);
        
        $data = [
            'title' => $post['subject'],
            'board' => $board,
            'post' => $post,
            'comments' => $comments,
            'prev' => $prevNext['prev'],
            'next' => $prevNext['next'],
            'files' => $files
        ];
        
        $this->view('bbs/' . $board['board_skin'] . '/view', $data);
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
            'title' => '글쓰기 - ' . $board['board_name'],
            'board' => $board,
            'mode' => 'write'
        ];
        
        $this->view('bbs/' . $board['board_skin'] . '/write', $data);
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
            'board_id' => $boardId,
            'subject' => $subject,
            'content' => $content,
            'category' => $category,
            'writer' => $_SESSION['username'] ?? '비회원',
            'writer_uid' => $_SESSION['user_id'] ?? null,
            'is_notice' => (isAdmin() && $this->post('is_notice') === 'Y') ? 'Y' : 'N',
            'is_secret' => ($this->post('use_secret') === 'Y') ? 'Y' : 'N'
        ];
        
        // 비회원 비밀번호
        if (!isLoggedIn()) {
            $password = $this->post('password');
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
                $this->boardModel->attachFiles($postId, $uploadedFiles);
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
        
        if (!$post || $post['board_id'] !== $boardId) {
            redirect('/bbs/' . $boardId);
        }
        
        // 수정 권한 체크 (작성자 또는 관리자)
        $canEdit = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($post['writer_uid'] == $_SESSION['user_id'] || isAdmin()) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            redirect('/bbs/' . $boardId . '/view/' . $uid);
        }
        
        $data = [
            'title' => '글수정 - ' . $board['board_name'],
            'board' => $board,
            'post' => $post,
            'mode' => 'edit'
        ];
        
        $this->view('bbs/' . $board['board_skin'] . '/write', $data);
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
        
        if (!$post || $post['board_id'] !== $boardId) {
            $this->json(['success' => false, 'message' => '존재하지 않는 게시물입니다.'], 404);
        }
        
        // 수정 권한 체크
        $canEdit = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($post['writer_uid'] == $_SESSION['user_id'] || isAdmin()) {
                $canEdit = true;
            }
        }
        
        if (!$canEdit) {
            $this->json(['success' => false, 'message' => '수정 권한이 없습니다.'], 403);
        }
        
        // 입력 데이터
        $updateData = [
            'subject' => cleanInput($this->post('subject')),
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
        
        // 파일 업로드 처리
        if ($board['use_upload'] === 'Y' && !empty($_FILES['files']['name'][0])) {
            require_once __DIR__ . '/../libs/FileUpload.php';
            $uploader = new FileUpload();
            $uploadedFiles = $uploader->upload($_FILES['files']);
            
            if (!empty($uploadedFiles)) {
                $this->boardModel->attachFiles($uid, $uploadedFiles);
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
        
        if (!$post || $post['board_id'] !== $boardId) {
            $this->json(['success' => false, 'message' => '존재하지 않는 게시물입니다.'], 404);
        }
        
        // 삭제 권한 체크
        $canDelete = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($post['writer_uid'] == $_SESSION['user_id'] || isAdmin()) {
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
        
        $commentData = [
            'board_id' => $boardId,
            'post_uid' => $postUid,
            'content' => $content,
            'writer' => $_SESSION['username'] ?? '비회원',
            'writer_uid' => $_SESSION['user_id'] ?? null
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
        // 댓글 조회
        $comment = getUidData("SELECT * FROM bbs_comment WHERE uid = ?", [$commentUid]);
        
        if (!$comment) {
            $this->json(['success' => false, 'message' => '존재하지 않는 댓글입니다.'], 404);
        }
        
        // 삭제 권한 체크
        $canDelete = false;
        if (isLoggedIn() && isset($_SESSION['user_id'])) {
            if ($comment['writer_uid'] == $_SESSION['user_id'] || isAdmin()) {
                $canDelete = true;
            }
        }
        
        if (!$canDelete) {
            $this->json(['success' => false, 'message' => '삭제 권한이 없습니다.'], 403);
        }
        
        $result = $this->boardModel->deleteComment($commentUid, $postUid);
        
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
}
