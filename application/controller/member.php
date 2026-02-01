<?php
/**
 * Member Controller
 * 회원 관련 컨트롤러
 */

class Member extends Controller {
    
    private $memberModel;
    
    public function __construct() {
        // 회원 모델 추후 구현 시 로드
    }
    
    /**
     * 기본 라우팅 (리다이렉트)
     */
    public function index() {
        $this->redirect('/member/mypage');
    }
    
    /**
     * 로그인 페이지
     */
    public function login() {
        // 이미 로그인된 경우
        if (isLoggedIn()) {
            $this->redirect('/');
        }
        
        $data = [
            'title' => '로그인'
        ];
        
        $this->view('member/login', $data);
    }
    
    /**
     * 로그인 처리
     */
    public function loginProcess() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/member/login');
        }
        
        $username = cleanInput($this->post('username'));
        $password = $this->post('password');
        
        // Rate limiting
        $rateLimit = rateLimit('login_' . getClientIP(), 5, 15);
        if (!$rateLimit['allowed']) {
            $this->json([
                'success' => false,
                'message' => '너무 많은 로그인 시도입니다. ' . $rateLimit['retry_after'] . '초 후 다시 시도해주세요.'
            ], 429);
        }
        
        // 사용자 조회
        $user = getUidData("SELECT * FROM member WHERE user_id = ? AND status = 'active' LIMIT 1", [$username]);
        
        if ($user && verifyPassword($password, $user['password'])) {
            // 로그인 성공
            $_SESSION['user_id'] = $user['uid'];
            $_SESSION['username'] = $user['user_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['nickname'] = $user['nickname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['level'] = $user['level'];
            $_SESSION['is_admin'] = ($user['level'] >= 9);
            
            // 마지막 로그인 시간 업데이트
            getDbUpdate('member', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'uid = ?', 
                [$user['uid']]
            );
            
            // 로그인 포인트 적립
            require_once __DIR__ . '/../models/PointModel.php';
            $pointModel = new PointModel();
            $pointModel->rewardLogin($user['uid']);
            
            $this->json([
                'success' => true,
                'message' => '로그인 성공',
                'redirect' => '/'
            ]);
        } else {
            // 로그인 실패
            $this->json([
                'success' => false,
                'message' => '아이디 또는 비밀번호가 일치하지 않습니다.'
            ], 401);
        }
    }
    
    /**
     * 로그아웃
     */
    public function logout() {
        session_destroy();
        $this->redirect('/');
    }
    
    /**
     * 회원가입 페이지
     */
    public function register() {
        // 이미 로그인된 경우
        if (isLoggedIn()) {
            $this->redirect('/');
        }
        
        // 가입 환경 설정 로드
        $varFile = __DIR__ . '/../config/var/member_join.var.php';
        $joinConfig = [];
        if (file_exists($varFile)) {
            include $varFile;
            $joinConfig = $join_config ?? [];
        }
        
        // 회원가입 허용 여부 확인
        if (($joinConfig['use_join'] ?? 'Y') === 'N') {
            $data = [
                'title' => '회원가입',
                'error' => '현재 회원가입을 받지 않습니다.'
            ];
            $this->view('error', $data);
            return;
        }
        
        $data = [
            'title' => '회원가입',
            'join_config' => $joinConfig,
            'required_fields' => $joinConfig['required_fields'] ?? ['user_id', 'password', 'email', 'name']
        ];
        
        $this->view('member/register', $data);
    }
    
    /**
     * 회원가입 처리
     */
    public function registerProcess() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/member/register');
        }
        
        // 가입 환경 설정 로드
        $varFile = __DIR__ . '/../config/var/member_join.var.php';
        $joinConfig = [];
        if (file_exists($varFile)) {
            include $varFile;
            $joinConfig = $join_config ?? [];
        }
        
        // 회원가입 허용 여부 확인
        if (($joinConfig['use_join'] ?? 'Y') === 'N') {
            $this->json([
                'success' => false,
                'message' => '현재 회원가입을 받지 않습니다.'
            ], 403);
            return;
        }
        
        $requiredFields = $joinConfig['required_fields'] ?? ['user_id', 'password', 'email', 'name'];
        $approvalType = $joinConfig['approval_type'] ?? 'auto';
        
        // 입력 데이터
        $username = cleanInput($this->post('username'));
        $email = cleanInput($this->post('email'));
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        $name = cleanInput($this->post('name'));
        $phone = cleanInput($this->post('phone'));
        $address = cleanInput($this->post('address'));
        $tel = cleanInput($this->post('tel'));
        
        // 유효성 검사
        $errors = [];
        
        if (empty($username) || strlen($username) < 4) {
            $errors[] = '아이디는 4자 이상이어야 합니다.';
        }
        
        if (in_array('email', $requiredFields)) {
            if (empty($email) || !validateEmail($email)) {
                $errors[] = '올바른 이메일 주소를 입력해주세요.';
            }
        }
        
        if (empty($password) || strlen($password) < 8) {
            $errors[] = '비밀번호는 8자 이상이어야 합니다.';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = '비밀번호가 일치하지 않습니다.';
        }
        
        if (in_array('name', $requiredFields) && empty($name)) {
            $errors[] = '이름을 입력해주세요.';
        }
        
        if (in_array('phone', $requiredFields) && empty($phone)) {
            $errors[] = '휴대폰번호를 입력해주세요.';
        }
        
        if (in_array('address', $requiredFields) && empty($address)) {
            $errors[] = '주소를 입력해주세요.';
        }
        
        if (in_array('tel', $requiredFields) && empty($tel)) {
            $errors[] = '연락처를 입력해주세요.';
        }
        
        // 중복 체크
        $existingUser = getUidData("SELECT uid FROM member WHERE user_id = ? OR email = ? LIMIT 1", [$username, $email]);
        if ($existingUser) {
            $errors[] = '이미 사용중인 아이디 또는 이메일입니다.';
        }
        
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'errors' => $errors
            ], 400);
            return;
        }
        
        // 승인 방식에 따른 상태 설정
        $status = ($approvalType === 'auto') ? 'active' : 'pending';
        
        // 회원 정보 저장
        $hashedPassword = hashPassword($password);
        $memberData = [
            'user_id' => $username,
            'password' => $hashedPassword,
            'email' => $email,
            'name' => $name,
            'nickname' => $name,
            'level' => 1,
            'status' => $status,
            'point' => 0
        ];
        
        // 선택적 필드 추가
        if (!empty($phone)) $memberData['phone'] = $phone;
        if (!empty($address)) $memberData['address'] = $address;
        if (!empty($tel)) $memberData['tel'] = $tel;
        
        $insertId = getDbInsert('member', $memberData);
        
        if ($insertId) {
            $message = ($status === 'pending') 
                ? '회원가입이 완료되었습니다. 관리자 승인 후 이용 가능합니다.' 
                : '회원가입이 완료되었습니다.';
            
            $this->json([
                'success' => true,
                'message' => $message,
                'redirect' => '/member/login'
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => '회원가입 처리 중 오류가 발생했습니다.'
            ], 500);
        }
    }
    
    /**
     * 마이페이지
     */
    public function mypage($tab = 'profile') {
        $this->requireLogin();
        
        $uid = $_SESSION['user_id'];
        $user = getUidData("SELECT * FROM member WHERE uid = ?", [$uid]);
        
        if (!$user) {
            $this->redirect('/member/login');
            return;
        }
        
        // 게시물, 댓글 수 조회
        $postCount = getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE member_uid = ? AND status = 'active'", [$uid]);
        $commentCount = getDbCnt("SELECT COUNT(*) FROM bbs_comment WHERE member_uid = ? AND status = 'active'", [$uid]);
        
        $user['post_count'] = $postCount;
        $user['comment_count'] = $commentCount;
        
        $data = [
            'title' => '마이페이지',
            'user' => $user,
            'active_tab' => $tab
        ];
        
        $this->view('member/mypage', $data);
    }
    
    /**
     * 프로필 사진 업로드
     */
    public function uploadProfileImage() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['profile_image'])) {
            $this->json(['success' => false, 'message' => '파일이 업로드되지 않았습니다.'], 400);
        }
        
        $file = $_FILES['profile_image'];
        
        // 이미지 파일만 허용
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            $this->json(['success' => false, 'message' => '이미지 파일만 업로드 가능합니다.'], 400);
        }
        
        // 파일 크기 제한 (5MB)
        if ($file['size'] > 5242880) {
            $this->json(['success' => false, 'message' => '파일 크기는 5MB 이하여야 합니다.'], 400);
        }
        
        // 업로드 디렉토리
        $uploadDir = PUBLIC_PATH . '/uploads/profiles/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // 파일명 생성
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $filePath = $uploadDir . '/' . $fileName;
        $dbPath = '/uploads/profiles/' . date('Y/m') . '/' . $fileName;
        
        // 기존 프로필 이미지 삭제
        $user = getUidData("SELECT profile_image FROM member WHERE uid = ?", [$_SESSION['user_id']]);
        if ($user && !empty($user['profile_image']) && file_exists(PUBLIC_PATH . $user['profile_image'])) {
            @unlink(PUBLIC_PATH . $user['profile_image']);
        }
        
        // 파일 이동
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // DB 업데이트
            $result = getDbUpdate('member', 
                ['profile_image' => $dbPath], 
                'uid = ?', 
                [$_SESSION['user_id']]
            );
            
            if ($result !== false) {
                $this->json([
                    'success' => true,
                    'message' => '프로필 사진이 변경되었습니다.',
                    'image_url' => $dbPath
                ]);
            } else {
                @unlink($filePath);
                $this->json(['success' => false, 'message' => 'DB 업데이트 중 오류가 발생했습니다.'], 500);
            }
        } else {
            $this->json(['success' => false, 'message' => '파일 업로드 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 회원 정보 수정
     */
    public function updateProfile() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/member/mypage');
        }
        
        $name = cleanInput($this->post('name'));
        $nickname = cleanInput($this->post('nickname'));
        $email = cleanInput($this->post('email'));
        $phone = cleanInput($this->post('phone'));
        $tel = cleanInput($this->post('tel'));
        $address = cleanInput($this->post('address'));
        
        $updateData = [
            'name' => $name,
            'nickname' => $nickname,
            'email' => $email,
            'phone' => $phone,
            'tel' => $tel,
            'address' => $address
        ];
        
        // 비밀번호 변경 요청이 있는 경우
        $newPassword = $this->post('new_password');
        if (!empty($newPassword)) {
            $currentPassword = $this->post('current_password');
            
            // 현재 비밀번호 확인
            $user = getUidData("SELECT password FROM member WHERE uid = ?", [$_SESSION['user_id']]);
            if (!verifyPassword($currentPassword, $user['password'])) {
                $this->json(['success' => false, 'message' => '현재 비밀번호가 일치하지 않습니다.'], 400);
            }
            
            $updateData['password'] = hashPassword($newPassword);
        }
        
        $result = getDbUpdate('member', $updateData, 'uid = ?', [$_SESSION['user_id']]);
        
        if ($result !== false) {
            // 세션 정보 업데이트
            $_SESSION['name'] = $name;
            $_SESSION['nickname'] = $nickname;
            $_SESSION['email'] = $email;
            
            $this->json([
                'success' => true,
                'message' => '회원 정보가 수정되었습니다.'
            ]);
        } else {
            $this->json(['success' => false, 'message' => '정보 수정 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 비밀번호 변경
     */
    public function changePassword() {
        $this->requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        $newPasswordConfirm = $input['new_password_confirm'] ?? '';
        
        // 유효성 검사
        if (empty($currentPassword) || empty($newPassword)) {
            $this->json(['success' => false, 'message' => '모든 필드를 입력해주세요.'], 400);
            return;
        }
        
        if ($newPassword !== $newPasswordConfirm) {
            $this->json(['success' => false, 'message' => '새 비밀번호가 일치하지 않습니다.'], 400);
            return;
        }
        
        if (strlen($newPassword) < 8) {
            $this->json(['success' => false, 'message' => '비밀번호는 8자 이상이어야 합니다.'], 400);
            return;
        }
        
        // 현재 비밀번호 확인
        $user = getUidData("SELECT password FROM member WHERE uid = ?", [$_SESSION['user_id']]);
        
        if (!password_verify($currentPassword, $user['password'])) {
            $this->json(['success' => false, 'message' => '현재 비밀번호가 일치하지 않습니다.'], 400);
            return;
        }
        
        // 비밀번호 변경
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $result = getDbUpdate('member', 
            ['password' => $hashedPassword], 
            'uid = ?', 
            [$_SESSION['user_id']]
        );
        
        if ($result !== false) {
            $this->json(['success' => true, 'message' => '비밀번호가 변경되었습니다.']);
        } else {
            $this->json(['success' => false, 'message' => '비밀번호 변경 중 오류가 발생했습니다.'], 500);
        }
    }
    
    /**
     * 활동 내역 조회 (AJAX)
     */
    public function activity($type = 'posts') {
        $this->requireLogin();
        
        $uid = $_SESSION['user_id'];
        $html = '';
        
        if ($type === 'posts') {
            // 내 게시물
            $posts = getDbArray("
                SELECT d.uid, d.title, d.view_count, d.reg_date,
                       i.bbs_id, bl.bbs_name
                FROM bbs_index i
                INNER JOIN bbs_data d ON i.data_uid = d.uid
                LEFT JOIN bbs_list bl ON i.bbs_id = bl.bbs_id
                WHERE i.member_uid = ?
                ORDER BY d.reg_date DESC
                LIMIT 20
            ", [$uid]);
            
            if (empty($posts)) {
                $html = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>작성한 게시물이 없습니다.</div>';
            } else {
                $html .= '<div class="list-group list-group-flush">';
                foreach ($posts as $post) {
                    $html .= '
                    <a href="/bbs/' . $post['bbs_id'] . '/view/' . $post['uid'] . '" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">' . xssFilter($post['title']) . '</h6>
                                <p class="mb-1 small text-muted">
                                    <span class="badge bg-secondary me-2">' . xssFilter($post['bbs_name']) . '</span>
                                    <i class="fas fa-eye me-1"></i>' . number_format($post['view_count']) . '
                                </p>
                            </div>
                            <small class="text-muted">' . date('Y-m-d', strtotime($post['reg_date'])) . '</small>
                        </div>
                    </a>';
                }
                $html .= '</div>';
            }
        } elseif ($type === 'comments') {
            // 내 댓글
            $comments = getDbArray("
                SELECT c.uid, c.data_uid, c.content, c.reg_date, c.bbs_id,
                       d.title as post_title
                FROM bbs_comment c
                LEFT JOIN bbs_data d ON c.data_uid = d.uid
                WHERE c.member_uid = ?
                ORDER BY c.reg_date DESC
                LIMIT 20
            ", [$uid]);
            
            if (empty($comments)) {
                $html = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>작성한 댓글이 없습니다.</div>';
            } else {
                $html .= '<div class="list-group list-group-flush">';
                foreach ($comments as $comment) {
                    $html .= '
                    <a href="/bbs/' . $comment['bbs_id'] . '/view/' . $comment['data_uid'] . '#comment-' . $comment['uid'] . '" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1 small text-muted">게시물: ' . xssFilter($comment['post_title']) . '</h6>
                                <p class="mb-1">' . nl2br(xssFilter(mb_substr($comment['content'], 0, 100))) . (mb_strlen($comment['content']) > 100 ? '...' : '') . '</p>
                            </div>
                            <small class="text-muted">' . date('Y-m-d', strtotime($comment['reg_date'])) . '</small>
                        </div>
                    </a>';
                }
                $html .= '</div>';
            }
        } elseif ($type === 'points') {
            // 포인트 내역
            $points = getDbArray("
                SELECT * FROM point_history
                WHERE member_uid = ?
                ORDER BY created_at DESC
                LIMIT 50
            ", [$uid]);
            
            if (empty($points)) {
                $html = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>포인트 내역이 없습니다.</div>';
            } else {
                $html .= '<div class="table-responsive">';
                $html .= '<table class="table table-hover">';
                $html .= '<thead><tr><th>날짜</th><th>내용</th><th class="text-end">포인트</th><th class="text-end">잔액</th></tr></thead>';
                $html .= '<tbody>';
                foreach ($points as $point) {
                    $pointClass = $point['point'] > 0 ? 'text-success' : 'text-danger';
                    $pointSign = $point['point'] > 0 ? '+' : '';
                    $html .= '
                    <tr>
                        <td>' . date('Y-m-d H:i', strtotime($point['created_at'])) . '</td>
                        <td>' . xssFilter($point['reason'] ?? '포인트 적립') . '</td>
                        <td class="text-end ' . $pointClass . ' fw-bold">' . $pointSign . number_format($point['point']) . '</td>
                        <td class="text-end">' . number_format($point['balance'] ?? 0) . '</td>
                    </tr>';
                }
                $html .= '</tbody></table></div>';
            }
        }
        
        echo $html;
        exit;
    }
    
    /**
     * 최근 알림 조회 (AJAX)
     */
    public function notificationsRecent() {
        $this->requireLogin();
        
        // 최근 10개 알림 조회
        $notifications = getDbArray("
            SELECT * FROM notifications 
            WHERE user_uid = ?
            ORDER BY created_at DESC
            LIMIT 10
        ", [$_SESSION['user_id']]);
        
        // 시간 포맷 처리
        foreach ($notifications as &$notif) {
            $notif['time_ago'] = timeAgo($notif['created_at']);
        }
        
        $this->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * 모든 알림 읽음 처리
     */
    public function notificationsMarkAllRead() {
        $this->requireLogin();
        
        $result = getDbUpdate('notifications', 
            ['is_read' => 'Y'], 
            'user_uid = ? AND is_read = ?', 
            [$_SESSION['user_id'], 'N']
        );
        
        $this->json([
            'success' => true,
            'message' => '모든 알림을 읽음 처리했습니다.'
        ]);
    }
    
    /**
     * 전체 알림 페이지
     */
    public function notifications() {
        $this->requireLogin();
        
        $page = $this->get('page') ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $total = getDbCnt("SELECT COUNT(*) FROM notifications WHERE user_uid = ?", [$_SESSION['user_id']]);
        
        $notifications = getDbArray("
            SELECT * FROM notifications 
            WHERE user_uid = ?
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", [$_SESSION['user_id']]);
        
        $data = [
            'title' => '알림',
            'notifications' => $notifications,
            'total' => $total,
            'current_page' => (int)$page,
            'total_pages' => ceil($total / $perPage)
        ];
        
        $this->view('member/notifications', $data);
    }
}
