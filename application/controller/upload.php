<?php
/**
 * Upload Controller
 * 파일 업로드 처리 컨트롤러
 */

class Upload extends Controller {
    
    /**
     * 게시판 이미지 업로드
     * URL: /upload/bbs/image
     */
    public function bbs() {
        $this->image('bbs');
    }
    
    /**
     * 페이지 이미지 업로드
     * URL: /upload/page/image
     */
    public function page() {
        $this->image('page');
    }
    
    /**
     * 이미지 업로드 처리
     * 
     * @param string $type 업로드 타입 (bbs, page)
     */
    private function image($type = 'bbs') {
        // POST 요청만 허용
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json([
                'error' => [
                    'message' => 'POST 요청만 허용됩니다.'
                ]
            ], 405);
            return;
        }
        
        // 파일 업로드 확인
        if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
            $this->json([
                'error' => [
                    'message' => '파일 업로드에 실패했습니다.'
                ]
            ], 400);
            return;
        }
        
        $file = $_FILES['upload'];
        
        // 파일 정보
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        
        // 파일 확장자 확인
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        
        if (!in_array($fileExt, $allowedExts)) {
            $this->json([
                'error' => [
                    'message' => '허용되지 않는 파일 형식입니다. (jpg, jpeg, png, gif, webp, bmp만 허용)'
                ]
            ], 400);
            return;
        }
        
        // 파일 크기 확인 (10MB 제한)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($fileSize > $maxSize) {
            $this->json([
                'error' => [
                    'message' => '파일 크기는 10MB를 초과할 수 없습니다.'
                ]
            ], 400);
            return;
        }
        
        // 이미지 파일인지 확인
        $imageInfo = getimagesize($fileTmpName);
        if ($imageInfo === false) {
            $this->json([
                'error' => [
                    'message' => '이미지 파일이 아닙니다.'
                ]
            ], 400);
            return;
        }
        
        // 업로드 경로 생성
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        
        // 타입별 경로 설정
        $uploadDir = __DIR__ . '/../../public/uploads/' . $type . '/image/' . $year . '/' . $month . '/' . $day;
        
        // 디렉토리 생성 (존재하지 않을 경우)
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $this->json([
                    'error' => [
                        'message' => '업로드 디렉토리를 생성할 수 없습니다.'
                    ]
                ], 500);
                return;
            }
        }
        
        // 파일명 생성 (중복 방지)
        $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
        $uploadPath = $uploadDir . '/' . $newFileName;
        
        // 파일 이동
        if (!move_uploaded_file($fileTmpName, $uploadPath)) {
            $this->json([
                'error' => [
                    'message' => '파일 업로드에 실패했습니다.'
                ]
            ], 500);
            return;
        }
        
        // 파일 권한 설정
        chmod($uploadPath, 0644);
        
        // 웹 접근 가능한 URL 생성
        $fileUrl = '/public/uploads/' . $type . '/image/' . $year . '/' . $month . '/' . $day . '/' . $newFileName;
        
        // 성공 응답
        $this->json([
            'url' => $fileUrl,
            'uploaded' => 1,
            'fileName' => $newFileName,
            'width' => $imageInfo[0],
            'height' => $imageInfo[1]
        ]);
    }
}
