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
        $data = [
            'title' => '소개',
            'content' => '이 사이트에 대한 소개입니다.'
        ];
        
        $this->view('home/about', $data);
    }
}
