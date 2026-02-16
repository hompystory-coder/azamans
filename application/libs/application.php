<?php
/**
 * Application Class
 * MVC 프레임워크의 핵심 라우팅 클래스
 * URL 구조: https://www.도메인.com/controller/method/param1/param2/param3...
 */

class Application {
    
    protected $controller = 'home';
    protected $method = 'index';
    protected $params = [];
    
    public function __construct() {
        $url = $this->parseUrl();
        
        // 1. 컨트롤러 결정
        if (isset($url[0]) && !empty($url[0])) {
            $controllerFile = __DIR__ . '/../controller/' . $url[0] . '.php';
            
            if (file_exists($controllerFile)) {
                $this->controller = $url[0];
                unset($url[0]);
            }
        }
        
        // 컨트롤러 파일 로드
        $controllerFile = __DIR__ . '/../controller/' . $this->controller . '.php';
        if (!file_exists($controllerFile)) {
            $this->show404();
            return;
        }
        
        require_once $controllerFile;
        
        // 컨트롤러 인스턴스 생성
        $controllerClass = ucfirst($this->controller);
        if (!class_exists($controllerClass)) {
            $this->show404();
            return;
        }
        
        $this->controller = new $controllerClass;
        
        // 2. 메서드 결정
        if (isset($url[1]) && !empty($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
            // 메서드가 없으면 파라미터로 처리 (404 표시하지 않음)
        }
        
        // 기본 index 메서드가 없으면 404
        if (!method_exists($this->controller, $this->method)) {
            $this->show404();
            return;
        }
        
        // 3. 파라미터 추출
        $this->params = $url ? array_values($url) : [];
        
        // 컨트롤러의 메서드 호출
        call_user_func_array([$this->controller, $this->method], $this->params);
    }
    
    /**
     * URL 파싱
     * @return array
     */
    protected function parseUrl() {
        // 1. $_GET['url']이 있으면 사용 (Nginx rewrite로 전달된 경우)
        if (isset($_GET['url'])) {
            $url = trim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            
            // 빈 문자열이면 홈
            if (empty($url)) {
                return [];
            }
            
            // Sitemap XML 파일 라우팅 처리
            $url = $this->handleSitemapRouting($url);
            
            $url = explode('/', $url);
            return $url;
        }
        
        // 2. REQUEST_URI에서 직접 파싱 (Nginx rewrite가 없는 경우)
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = $_SERVER['REQUEST_URI'];
            
            // 쿼리스트링 제거
            if (($pos = strpos($requestUri, '?')) !== false) {
                $requestUri = substr($requestUri, 0, $pos);
            }
            
            // 앞뒤 슬래시 제거
            $requestUri = trim($requestUri, '/');
            
            // index.php 제거
            $requestUri = str_replace('index.php', '', $requestUri);
            $requestUri = trim($requestUri, '/');
            
            // 빈 문자열이면 홈
            if (empty($requestUri)) {
                return [];
            }
            
            // Sitemap XML 파일 라우팅 처리
            $requestUri = $this->handleSitemapRouting($requestUri);
            
            // URL을 배열로 변환
            $url = filter_var($requestUri, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        
        return [];
    }
    
    /**
     * Sitemap XML 라우팅 처리
     * sitemap_index.xml -> sitemap/index
     * sitemap_news.xml -> sitemap/news
     * sitemap_bbs.xml -> sitemap/bbs
     * sitemap_{type}_{year}_{month}.xml -> sitemap/monthly/{type}/{year}/{month}
     * 
     * @param string $url
     * @return string
     */
    protected function handleSitemapRouting($url) {
        // sitemap_index.xml
        if ($url === 'sitemap_index.xml') {
            return 'sitemap/index';
        }
        
        // sitemap_news.xml
        if ($url === 'sitemap_news.xml') {
            return 'sitemap/news';
        }
        
        // sitemap_bbs.xml
        if ($url === 'sitemap_bbs.xml') {
            return 'sitemap/bbs';
        }
        
        // sitemap_{type}_{year}_{month}.xml
        if (preg_match('/^sitemap_(news|bbs)_(\d{4})_(\d{2})\.xml$/', $url, $matches)) {
            return 'sitemap/monthly/' . $matches[1] . '/' . $matches[2] . '/' . $matches[3];
        }
        
        return $url;
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
