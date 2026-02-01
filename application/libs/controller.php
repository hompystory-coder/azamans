<?php
/**
 * Base Controller Class
 * 모든 컨트롤러의 부모 클래스
 */

class Controller {
    
    /**
     * 뷰 렌더링
     * @param string $view 뷰 파일 경로 (예: 'home/index')
     * @param array $data 뷰에 전달할 데이터
     */
    protected function view($view, $data = []) {
        // 데이터를 변수로 추출
        extract($data);
        
        // 뷰 파일 경로
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("뷰 파일을 찾을 수 없습니다: " . $view);
        }
    }
    
    /**
     * JSON 응답 반환
     * @param array $data
     * @param int $statusCode
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * 리다이렉트
     * @param string $url
     */
    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
    
    /**
     * 모델 로드
     * @param string $model 모델명
     * @return object
     */
    protected function model($model) {
        $modelFile = __DIR__ . '/../models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("모델을 찾을 수 없습니다: " . $model);
        }
    }
    
    /**
     * POST 데이터 가져오기
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key, $default = '') {
        return $_POST[$key] ?? $default;
    }
    
    /**
     * GET 데이터 가져오기
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key, $default = '') {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * 로그인 확인
     * @param string $redirectUrl 미로그인 시 리다이렉트 URL
     */
    protected function requireLogin($redirectUrl = '/member/login') {
        if (!isLoggedIn()) {
            $this->redirect($redirectUrl);
        }
    }
    
    /**
     * 관리자 권한 확인
     * @param string $redirectUrl 권한 없을 시 리다이렉트 URL
     */
    protected function requireAdmin($redirectUrl = '/') {
        if (!isAdmin()) {
            $this->redirect($redirectUrl);
        }
    }
    
    /**
     * 404 페이지 표시
     */
    protected function show404() {
        http_response_code(404);
        $viewFile = __DIR__ . '/../views/errors/404.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo '<h1>404 - 페이지를 찾을 수 없습니다</h1>';
            echo '<p>요청하신 페이지가 존재하지 않습니다.</p>';
        }
        exit;
    }
}
