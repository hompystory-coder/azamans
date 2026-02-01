<?php
/**
 * Policy Controller
 * 약관 및 정책 페이지 컨트롤러
 */

class Policy extends Controller {
    
    /**
     * 이용약관
     */
    public function terms() {
        $data = [
            'title' => '이용약관',
            'content' => getConfig('terms_of_service', '이용약관 내용이 없습니다.')
        ];
        
        $this->view('policy/terms', $data);
    }
    
    /**
     * 개인정보보호정책
     */
    public function privacy() {
        $data = [
            'title' => '개인정보보호정책',
            'content' => getConfig('privacy_policy', '개인정보보호정책 내용이 없습니다.')
        ];
        
        $this->view('policy/privacy', $data);
    }
    
    /**
     * 청소년보호정책
     */
    public function youth() {
        $data = [
            'title' => '청소년보호정책',
            'content' => getConfig('youth_protection', '청소년보호정책 내용이 없습니다.')
        ];
        
        $this->view('policy/youth', $data);
    }
}
