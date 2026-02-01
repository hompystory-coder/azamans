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
        // 1. 파일에서 로드 (우선순위)
        $pageFilePath = __DIR__ . '/../../public/uploads/page/' . $menuId . '.php';
        
        if (file_exists($pageFilePath)) {
            // 파일에서 로드 (빠름!)
            ob_start();
            include $pageFilePath;
            $content = ob_get_clean();
        } else {
            // 파일이 없으면 DB에서 로드 (폴백)
            $page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$menuId]);
            $content = $page['content'] ?? '<p>페이지 내용이 없습니다.</p>';
            
            // DB에 내용이 있으면 파일로 저장
            if (!empty($page['content'])) {
                $pageFileContent = '<?php
/**
 * 메뉴 페이지: ' . $menu['menu_name'] . '
 * 메뉴 ID: ' . $menuId . '
 * 생성일: ' . date('Y-m-d H:i:s') . '
 * 
 * 이 파일은 자동 생성되었습니다.
 * 관리자 페이지에서 수정하세요: /admin/editMenu/' . $menuId . '
 */
?>
' . $page['content'];
                
                file_put_contents($pageFilePath, $pageFileContent);
                chmod($pageFilePath, 0644);
            }
        }
        
        // 첨부파일 목록 조회
        $pageFiles = getDbArray("SELECT * FROM page_files WHERE menu_id = ? ORDER BY id ASC", [$menuId]);
        
        $data = [
            'title' => xssFilter($menu['menu_name']),
            'menu' => $menu,
            'content' => $content,
            'pageFiles' => $pageFiles
        ];
        
        $this->view('page/view', $data);
    }
    
    /**
     * 첨부파일 다운로드
     */
    public function download($fileId = null) {
        if (!$fileId) {
            $this->show404();
            return;
        }
        
        // 파일 정보 조회
        $file = getUidData("SELECT * FROM page_files WHERE id = ?", [$fileId]);
        
        if (!$file) {
            $this->show404();
            return;
        }
        
        // 실제 파일 경로
        $filePath = __DIR__ . '/../../public/uploads/page/files/' . $file['saved_name'];
        
        if (!file_exists($filePath)) {
            $this->show404();
            return;
        }
        
        // 다운로드 횟수 증가
        getDbUpdate('page_files', ['download_count' => $file['download_count'] + 1], 'id = ?', [$fileId]);
        
        // 파일 다운로드
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
