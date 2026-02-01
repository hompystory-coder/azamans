<?php
/**
 * Home Controller
 * 메인 페이지 컨트롤러
 */

class Home extends Controller {
    
    /**
     * 메인 페이지
     */
    public function index() {
        $data = [
            'title' => '메인 페이지',
            'site_name' => getConfig('site_name', 'MVC Framework'),
            'description' => 'PHP MVC Framework 기본 구조'
        ];
        
        $this->view('home/index', $data);
    }
    
    /**
     * About 페이지
     */
    public function about() {
        // admin_config에서 소개 페이지 내용 가져오기
        $aboutContent = getConfig('about_content', '');
        $aboutTitle = getConfig('about_title', '사이트 소개');
        
        // 소개 내용이 없으면 기본 내용 사용
        if (empty($aboutContent)) {
            $aboutContent = '
                <h2>환영합니다!</h2>
                <p>이 사이트는 PHP MVC 패턴으로 구축된 웹 애플리케이션입니다.</p>
                <p>다양한 기능을 제공하며, 사용자 친화적인 인터페이스를 제공합니다.</p>
                <h3>주요 기능</h3>
                <ul>
                    <li>게시판 시스템</li>
                    <li>회원 관리</li>
                    <li>파일 업로드</li>
                    <li>댓글 시스템</li>
                    <li>포인트 시스템</li>
                    <li>알림 시스템</li>
                </ul>
            ';
        }
        
        $data = [
            'title' => $aboutTitle,
            'about_title' => $aboutTitle,
            'content' => $aboutContent
        ];
        
        $this->view('home/about', $data);
    }
    
    /**
     * CKEditor 5 테스트 페이지
     */
    public function testEditor() {
        $data = [
            'title' => 'CKEditor 5 테스트'
        ];
        
        $this->view('home/test_editor', $data);
    }
}
