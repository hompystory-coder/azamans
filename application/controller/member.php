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
        $user = getUidData("SELECT * FROM member WHERE username = ? AND status = 'active' LIMIT 1", [$username]);
        
        if ($user && verifyPassword($password, $user['password'])) {
            // 로그인 성공
            $_SESSION['user_id'] = $user['uid'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['level'] = $user['level'];
            $_SESSION['is_admin'] = ($user['level'] >= 9);
            
            // 마지막 로그인 시간 업데이트
            getDbUpdate('member', 
                ['last_login' => date('Y-m-d H:i:s')], 
                'uid = ?', 
                [$user['uid']]
            );
            
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
        
        $data = [
            'title' => '회원가입'
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
        
        // 입력 데이터
        $username = cleanInput($this->post('username'));
        $email = cleanInput($this->post('email'));
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        $name = cleanInput($this->post('name'));
        
        // 유효성 검사
        $errors = [];
        
        if (empty($username) || strlen($username) < 4) {
            $errors[] = '아이디는 4자 이상이어야 합니다.';
        }
        
        if (!validateEmail($email)) {
            $errors[] = '올바른 이메일 주소를 입력해주세요.';
        }
        
        if (empty($password) || strlen($password) < 8) {
            $errors[] = '비밀번호는 8자 이상이어야 합니다.';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = '비밀번호가 일치하지 않습니다.';
        }
        
        // 중복 체크
        $existingUser = getUidData("SELECT uid FROM member WHERE username = ? OR email = ? LIMIT 1", [$username, $email]);
        if ($existingUser) {
            $errors[] = '이미 사용중인 아이디 또는 이메일입니다.';
        }
        
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'errors' => $errors
            ], 400);
        }
        
        // 회원 정보 저장
        $hashedPassword = hashPassword($password);
        $insertId = getDbInsert('member', [
            'username' => $username,
            'password' => $hashedPassword,
            'email' => $email,
            'name' => $name,
            'level' => 1,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($insertId) {
            $this->json([
                'success' => true,
                'message' => '회원가입이 완료되었습니다.',
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
    public function mypage() {
        $this->requireLogin();
        
        $user = getUidData("SELECT * FROM member WHERE uid = ?", [$_SESSION['user_id']]);
        
        $data = [
            'title' => '마이페이지',
            'user' => $user
        ];
        
        $this->view('member/mypage', $data);
    }
}
