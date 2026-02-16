<?php
/**
 * Plugin Controller
 * 플러그인 컨트롤러 (사용자용)
 */

class Plugin extends Controller {
    
    public function __construct() {
        // 플러그인은 로그인 사용자만 접근 가능하도록 설정 (필요시)
    }
    
    /**
     * 플러그인 라우팅
     * URL: /plugin/{pluginId}
     */
    public function index($pluginId = null) {
        if (!$pluginId) {
            redirect('/');
            return;
        }
        
        // 플러그인별 분기
        switch ($pluginId) {
            case 'autopost':
                return $this->autopost();
            case 'videocreate':
                return $this->videocreate();
            case 'trendposting':
                return $this->trendposting();
            default:
                redirect('/');
        }
    }
    
    /**
     * 자동포스팅 사용자 페이지
     */
    private function autopost() {
        $this->title = '자동포스팅';
        $this->render('plugin/autopost');
    }
    
    /**
     * 동영상생성 사용자 페이지
     */
    private function videocreate() {
        $this->title = '동영상생성';
        $this->render('plugin/videocreate');
    }
    
    /**
     * 트렌드포스팅 사용자 페이지
     */
    private function trendposting() {
        $this->title = '트렌드포스팅';
        $this->render('plugin/trendposting');
    }
}
