<?php
/**
 * Page Controller
 * 메뉴 페이지 컨트롤러
 */

class Page extends Controller {
    
    /**
     * 페이지 보기
     */
    public function index($menuId = null) {
        if (!$menuId) {
            $this->show404();
            return;
        }
        
        // 메뉴 정보 조회
        $menu = getUidData("SELECT * FROM header_menu WHERE id = ? AND menu_type = 'page'", [$menuId]);
        
        if (!$menu) {
            $this->show404();
            return;
        }
        
        // 차단 확인
        if ($menu['is_blocked'] === 'Y') {
            $this->show404();
            return;
        }
        
        // 페이지 콘텐츠 조회
        $page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$menuId]);
        $content = $page['content'] ?? '<p>페이지 내용이 없습니다.</p>';
        
        $data = [
            'title' => xssFilter($menu['menu_name']),
            'menu' => $menu,
            'content' => $content
        ];
        
        $this->view('page/view', $data);
    }
}
