<?php
/**
 * Content Controller
 * 콘텐츠 컨트롤러
 */

class Content extends Controller {
    
    /**
     * 콘텐츠 보기
     */
    public function index($contentFile = null) {
        if (!$contentFile) {
            $this->show404();
            return;
        }
        
        // 콘텐츠 파일 경로
        $filePath = __DIR__ . '/../views/content/' . $contentFile . '.php';
        
        // 파일 존재 확인
        if (!file_exists($filePath)) {
            $this->show404();
            return;
        }
        
        // 콘텐츠 로드
        $data = [
            'title' => ucfirst($contentFile),
            'contentFile' => $contentFile
        ];
        
        $this->view('content/' . $contentFile, $data);
    }
}
