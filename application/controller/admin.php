<?php
/**
 * Admin Controller
 * 관리자 페이지 컨트롤러
 */

class Admin extends Controller {
    
    public function __construct() {
        // 관리자 권한 체크
        if (!isLoggedIn()) {
            redirect('/member/login?redirect=/admin');
            exit;
        }
        
        if (!isAdmin()) {
            redirect('/');
            exit;
        }
    }
    
    /**
     * Public wrapper for view() - 외부 함수에서 사용 가능
     * @param string $view 뷰 경로
     * @param array $data 데이터
     */
    public function renderView($view, $data = []) {
        $this->view($view, $data);
    }
    
    /**
     * Public wrapper for json() - 외부 함수에서 사용 가능
     * @param array $data
     * @param int $statusCode
     */
    public function renderJson($data, $statusCode = 200) {
        $this->json($data, $statusCode);
    }
    
    /**
     * Public wrapper for get() - 외부 함수에서 사용 가능
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getParam($key, $default = '') {
        return $this->get($key, $default);
    }
    
    /**
     * Public wrapper for post() - 외부 함수에서 사용 가능
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function postParam($key, $default = '') {
        return $this->post($key, $default);
    }
    
    /**
     * 관리자 대시보드
     */
    public function index() {
        require_once APP_PATH . '/libs/admin_index_func.php';
        admin_index_handler($this);
    }
    
    /**
     * 사이트 설정
     */
    public function config($action = '') {
        require_once APP_PATH . '/libs/admin_config_func.php';
        admin_config_handler($this, $action);
    }
    
    /**
     * 기본 설정 저장
     */
    
    /**
     * 로고 업로드
     */
    
    /**
     * 로고 크기 저장
     */
    
    /**
     * 로고 삭제
     */
    
    /**
     * 사이트 설정 저장
     */
    
    public function configSave() {
        require_once APP_PATH . '/libs/admin_config_func.php';
        admin_config_save_handler($this);
    }

    /**
     * 회원 관리
     */
    public function members($page = 1) {
        require_once APP_PATH . '/libs/admin_member_func.php';
        admin_members_list_handler($this, $page);
    }
    
    
    /**
     * 회원 상세/수정
     */
    public function member($uid, $action = '') {
        require_once APP_PATH . '/libs/admin_member_func.php';
        admin_member_detail_handler($this, $uid, $action);
    }
    
    
    /**
     * 회원 정보 수정
     */
    
    /**
     * 비밀번호 재설정
     */
    
    /**
     * 회원 삭제
     */
    
    /**
     * 게시판 관리
     */
    public function boards() {
        require_once APP_PATH . '/libs/admin_board_func.php';
        admin_boards_handler($this);
    }
    
    
    /**
     * 게시판 수정
     */
    public function board($uid) {
        require_once APP_PATH . '/libs/admin_board_func.php';
        admin_board_detail_handler($this, $uid);
    }
    
    public function statistics() {
        require_once APP_PATH . '/libs/admin_statistics_func.php';
        admin_statistics_handler($this);
    }

    
    /**
     * AJAX 통계 데이터
     */
    public function statisticsData() {
        require_once APP_PATH . '/libs/admin_statistics_func.php';
        admin_statistics_data_handler($this);
    }

    
    /**
     * 사이트 설정 - 파비콘
     */
    public function favicon($action = null) {
        require_once APP_PATH . '/libs/admin_config_func.php';
        admin_favicon_handler($this, $action);
    }

    
    /**
     * 사이트 설정 - 헤더 코드
     */
    public function headercode() {
        require_once APP_PATH . '/libs/admin_config_func.php';
        admin_headercode_handler($this);
    }

    
    /**
     * 사이트 설정 - 푸터 코드
     */
    public function footercode() {
        require_once APP_PATH . '/libs/admin_config_func.php';
        admin_footercode_handler($this);
    }

    
    /**
     * 사이트 설정 - RSS
     */
    public function rss($action = null) {
        require_once APP_PATH . '/libs/admin_rss_func.php';
        admin_rss_handler($this, $action);
    }

    /**
     * 사이트 설정 - Sitemap
     */
    public function sitemap($action = null) {
        require_once APP_PATH . '/libs/admin_sitemap_func.php';
        admin_sitemap_handler($this, $action);
    }

    
    /**
     * SEO 설정
     */
    public function seo($action = null) {
        require_once APP_PATH . '/libs/admin_seo_func.php';
        admin_seo_handler($this, $action);
    }

    
    /**
     * BOT 설정
     */
    public function bot($action = null) {
        require_once APP_PATH . '/libs/admin_bot_func.php';
        admin_bot_handler($this, $action);
    }
    
    /**
     * robots.txt 파일 생성 (하위 호환성 유지)
     */
    private function generateRobotsTxt($allowedBots = null) {
        require_once APP_PATH . '/libs/admin_bot_func.php';
        return admin_bot_generate_robots_txt($allowedBots);
    }
    
    /**
     * 현재 robots.txt 내용 가져오기 (하위 호환성 유지)
     */
    private function getRobotsTxtContent() {
        require_once APP_PATH . '/libs/admin_bot_func.php';
        return admin_bot_get_robots_txt_content();
    }
    
    /**
     * robots.txt에서 허용된 봇 추출 (하위 호환성 유지)
     */
    private function extractAllowedBotsFromRobotsTxt() {
        require_once APP_PATH . '/libs/admin_bot_func.php';
        return admin_bot_extract_allowed_bots_from_robots_txt();
    }
    
    /**
     * 회원가입 설정
     */
    public function joinconfig($action = '') {
        require_once APP_PATH . '/libs/admin_member_func.php';
        admin_joinconfig_handler($this, $action);
    }

    
    /**
     * 회원 등급 관리
     */
    public function levels() {
        require_once APP_PATH . '/libs/admin_member_func.php';
        admin_levels_handler($this);
    }

    
    /**
     * 회원 포인트 지급
     */
    public function points() {
        require_once APP_PATH . '/libs/admin_member_func.php';
        admin_points_handler($this);
    }

    
    /**
     * 게시물 리스트
     */
    public function posts() {
        require_once APP_PATH . '/libs/admin_post_func.php';
        admin_posts_handler($this);
    }
    
    /**
     * 게시물 삭제 (DELETE /admin/posts/{uid})
     */
    public function deletePost($uid) {
        require_once APP_PATH . '/libs/admin_post_func.php';
        admin_post_delete_handler($this, $uid);
    }
    
    /**
     * 댓글 리스트
     */
    public function comments() {
        require_once APP_PATH . '/libs/admin_post_func.php';
        admin_comments_handler($this);
    }
    
    /**
     * 방문자 통계 (일별/월별)
     */
    public function visitor() {
        require_once APP_PATH . '/libs/admin_visitor_func.php';
        admin_visitor_handler($this);
    }
    
    /**
     * 방문자 추적
     */
    public function tracking() {
        require_once APP_PATH . '/libs/admin_visitor_func.php';
        admin_tracking_handler($this);
    }
    
    /**
     * 게시물 통계
     */
    public function poststats() {
        require_once APP_PATH . '/libs/admin_post_func.php';
        admin_poststats_handler($this);
    }
    
    /**
     * 헤더 메뉴 관리
     */
    public function menu($action = 'header') {
        if ($action === 'header') {
            require_once APP_PATH . '/libs/admin_header_menu_func.php';
            admin_header_menu_handler($this);
        } elseif ($action === 'footer') {
            require_once APP_PATH . '/libs/admin_footer_menu_func.php';
            admin_footer_menu_handler($this);
        }
    }
    
    /**
     * 메뉴 생성 (헤더/푸터 공통, 콤마로 여러 개 생성 가능)
     */
    public function createMenu() {
        $menuType = $_POST['menu_type'] ?? 'header';
        
        if ($menuType === 'footer') {
            require_once APP_PATH . '/libs/admin_footer_menu_func.php';
            admin_footer_menu_create_handler($this);
        } else {
            require_once APP_PATH . '/libs/admin_header_menu_func.php';
            admin_header_menu_create_handler($this);
        }
    }
    
    /**
     * 메뉴 삭제 (헤더/푸터 자동 판별)
     */
    /**
     * 헤더 메뉴 삭제
     */
    public function deleteMenu($id = null) {
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid menu ID'], 400);
            return;
        }
        
        require_once APP_PATH . '/libs/admin_header_menu_func.php';
        admin_header_menu_delete_handler($this, $id);
    }
    
    /**
     * 푸터 메뉴 삭제
     */
    public function deleteFooterMenu($id = null) {
        if (!$id) {
            $this->json(['success' => false, 'message' => 'Invalid menu ID'], 400);
            return;
        }
        
        require_once APP_PATH . '/libs/admin_footer_menu_func.php';
        admin_footer_menu_delete_handler($this, $id);
    }
    private function OLD_deleteMenu_REMOVED() {
        if (false) {
            $this->json(['success' => true, 'message' => '메뉴가 삭제되었습니다.']);
        } else {
            // 이 부분은 위에서 제거됨
        }
    }
    
    /**
     * 푸터 메뉴 개별 삭제 (deleteFooterMenu 통합됨)
     */
    
    /**
     * 헤더 메뉴 순서 변경
     */
    public function updateMenuOrder() {
        require_once APP_PATH . '/libs/admin_header_menu_func.php';
        admin_header_menu_update_order_handler($this);
    }
    
    /**
     * 메뉴 수정 페이지
     */
    public function editMenu($id = null) {
        require_once APP_PATH . '/libs/admin_header_menu_func.php';
        admin_header_menu_edit_handler($this, $id);
    }
    
    /**
     * 메뉴 업데이트
     */
    public function updateMenu($id = null) {
        $debugLog = BASE_PATH . '/menu_update_debug.log';
        file_put_contents($debugLog, date('Y-m-d H:i:s') . " - CONTROLLER: updateMenu called with ID: $id\n", FILE_APPEND);
        file_put_contents($debugLog, "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n", FILE_APPEND);
        file_put_contents($debugLog, "Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . "\n", FILE_APPEND);
        
        require_once APP_PATH . '/libs/admin_header_menu_func.php';
        file_put_contents($debugLog, "CONTROLLER: About to call handler\n", FILE_APPEND);
        admin_header_menu_update_handler($this, $id);
        file_put_contents($debugLog, "CONTROLLER: Handler returned\n", FILE_APPEND);
    }
    
    /**
     * 페이지 첨부파일 삭제
     */
    public function deletePageFile($fileId = null, $menuType = 'header') {
        if ($menuType === 'footer') {
            require_once APP_PATH . '/libs/admin_footer_menu_func.php';
            admin_footer_menu_page_file_delete_handler($this, $fileId);
        } else {
            require_once APP_PATH . '/libs/admin_header_menu_func.php';
            admin_menu_page_file_delete_handler($this, $fileId);
        }
    }
    
    /**
     * 서브메뉴 추가
     */
    public function addSubmenu($parentId = null) {
        require_once APP_PATH . '/libs/admin_header_menu_func.php';
        admin_header_submenu_add_handler($this, $parentId);
    }
    
    /**
     * 이미지 설정 저장
     */
    
    /**
     * 워터마크 설정 저장
     */
    
    /**
     * 워터마크 이미지 업로드
     */
    public function createFooterMenu() {
        require_once APP_PATH . '/libs/admin_footer_menu_func.php';
        admin_footer_menu_create_handler($this);
    }
    
    /**
     * 푸터 메뉴 삭제
     */
    /**
     * 중복 제거됨 - 위의 deleteFooterMenu() 사용
     */
    
    /**
     * 푸터 메뉴 순서 업데이트
     */
    public function updateFooterMenuOrder() {
        require_once APP_PATH . '/libs/admin_footer_menu_func.php';
        admin_footer_menu_update_order_handler($this);
    }
    
    /**
     * 푸터 서브메뉴 추가
     */
    public function addFooterSubmenu($parentId = null) {
        require_once APP_PATH . '/libs/admin_footer_menu_func.php';
        admin_footer_submenu_add_handler($this, $parentId);
    }
    
    /**
     * 푸터 메뉴 수정 페이지
     */
    public function editFooterMenu($id = null) {
        require_once APP_PATH . '/libs/admin_footer_menu_func.php';
        admin_footer_menu_edit_handler($this, $id);
    }
    
    /**
     * 푸터 메뉴 수정 처리
     */
    public function updateFooterMenu($id = null) {
        require_once APP_PATH . '/libs/admin_footer_menu_func.php';
        admin_footer_menu_update_handler($this, $id);
    }
    
    /**
     * 뉴스 목록
     */
    public function news($uid = null) {
        require_once APP_PATH . '/libs/admin_news_func.php';
        
        // 뉴스 상세
        if ($uid !== null) {
            admin_news_detail_handler($this, $uid);
            return;
        }
        
        // 뉴스 목록
        admin_news_list_handler($this);
    }
    

    
    /**
     * 뉴스 포스트 관리
     */
    public function newsposts() {
        require_once APP_PATH . '/libs/admin_news_func.php';
        admin_news_posts_handler($this);
    }
    
    /**
     * 뉴스 댓글 관리
     */
    public function newscomments() {
        require_once APP_PATH . '/libs/admin_news_func.php';
        admin_news_comments_handler($this);
    }
    
    /**
     * 플러그인 관리
     */
    public function plugin($pluginId = null) {
        require_once APP_PATH . '/libs/admin_plugin_func.php';
        admin_plugin_handler($this, $pluginId);
    }
}




