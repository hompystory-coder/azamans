<?php
/**
 * File Upload Helper
 * 파일 업로드 처리 클래스
 */

class FileUpload {
    
    private $uploadDir;
    private $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 
        'application/zip', 'application/x-zip-compressed',
        'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain'
    ];
    private $maxFileSize = 10485760; // 10MB
    
    public function __construct($uploadDir = null) {
        $this->uploadDir = $uploadDir ?? PUBLIC_PATH . '/uploads';
        
        // 업로드 디렉토리 생성
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        // 날짜별 디렉토리 생성 (년/월/일)
        $dayDir = $this->uploadDir . '/' . date('Y/m/d');
        if (!is_dir($dayDir)) {
            mkdir($dayDir, 0755, true);
        }
    }
    
    /**
     * 파일 업로드 처리
     * @param array $files $_FILES 배열
     * @return array 업로드 결과
     */
    public function upload($files) {
        $results = [];
        
        if (empty($files['name'][0])) {
            return $results;
        }
        
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $result = $this->uploadSingle($file);
            if ($result['success']) {
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    /**
     * 단일 파일 업로드
     */
    private function uploadSingle($file) {
        // 에러 체크
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => '파일 업로드 중 오류가 발생했습니다.',
                'error_code' => $file['error']
            ];
        }
        
        // 파일 크기 체크
        if ($file['size'] > $this->maxFileSize) {
            return [
                'success' => false,
                'message' => '파일 크기가 너무 큽니다. (최대: ' . $this->formatBytes($this->maxFileSize) . ')'
            ];
        }
        
        // MIME 타입 체크
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return [
                'success' => false,
                'message' => '허용되지 않는 파일 형식입니다.'
            ];
        }
        
        // 파일명 생성
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateFilename($extension);
        $filepath = date('Y/m/d') . '/' . $filename;
        $fullPath = $this->uploadDir . '/' . $filepath;
        
        // 파일 이동
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            return [
                'success' => false,
                'message' => '파일 저장 중 오류가 발생했습니다.'
            ];
        }
        
        $result = [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'mime_type' => $mimeType,
            'url' => '/public/uploads/' . $filepath
        ];
        
        // 이미지 파일인 경우 썸네일 생성 및 워터마크 적용
        if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            try {
                // ImageProcessor 로드
                require_once __DIR__ . '/ImageProcessor.php';
                $imageProcessor = new ImageProcessor();
                
                // 이미지 처리 (리사이즈, 썸네일 생성, 워터마크)
                $processResult = $imageProcessor->processImage($fullPath, $filename);
                
                if ($processResult['success']) {
                    $result['thumbnails'] = [
                        'big' => $processResult['big'],
                        'middle' => $processResult['middle'],
                        'small' => $processResult['small']
                    ];
                    
                    // 원본이 리사이즈되었다면 업데이트
                    if ($processResult['original'] && $processResult['original'] !== $filename) {
                        $result['filename'] = $processResult['original'];
                        $result['filepath'] = date('Y/m/d') . '/' . $processResult['original'];
                    }
                }
            } catch (Exception $e) {
                // 이미지 처리 실패해도 원본 파일은 업로드 성공으로 처리
                error_log('이미지 처리 실패: ' . $e->getMessage());
            }
        }
        
        return $result;
    }
    
    /**
     * 안전한 파일명 생성
     */
    private function generateFilename($extension) {
        return uniqid('file_', true) . '.' . strtolower($extension);
    }
    
    /**
     * 파일 삭제
     */
    public function delete($filepath) {
        $fullPath = $this->uploadDir . '/' . $filepath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
    
    /**
     * 이미지 리사이즈
     */
    public function resizeImage($filepath, $maxWidth = 800, $maxHeight = 800) {
        $fullPath = $this->uploadDir . '/' . $filepath;
        
        if (!file_exists($fullPath)) {
            return false;
        }
        
        $imageInfo = getimagesize($fullPath);
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // 리사이즈 불필요
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }
        
        // 비율 계산
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        // 원본 이미지 로드
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($fullPath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($fullPath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($fullPath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($fullPath);
                break;
            default:
                return false;
        }
        
        // 새 이미지 생성
        $dest = imagecreatetruecolor($newWidth, $newHeight);
        
        // 투명도 유지 (PNG, GIF)
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
        }
        
        // 리사이즈
        imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // 저장
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($dest, $fullPath, 85);
                break;
            case 'image/png':
                imagepng($dest, $fullPath, 8);
                break;
            case 'image/gif':
                imagegif($dest, $fullPath);
                break;
            case 'image/webp':
                imagewebp($dest, $fullPath, 85);
                break;
        }
        
        // 메모리 해제
        imagedestroy($source);
        imagedestroy($dest);
        
        return true;
    }
    
    /**
     * 썸네일 생성
     */
    public function createThumbnail($filepath, $thumbWidth = 200, $thumbHeight = 200) {
        $fullPath = $this->uploadDir . '/' . $filepath;
        
        if (!file_exists($fullPath)) {
            return false;
        }
        
        $pathInfo = pathinfo($filepath);
        $thumbPath = $pathInfo['dirname'] . '/thumb_' . $pathInfo['basename'];
        $fullThumbPath = $this->uploadDir . '/' . $thumbPath;
        
        $imageInfo = getimagesize($fullPath);
        $mimeType = $imageInfo['mime'];
        
        // 원본 이미지 로드
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($fullPath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($fullPath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($fullPath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($fullPath);
                break;
            default:
                return false;
        }
        
        // 정사각형 썸네일 생성
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
        }
        
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, imagesx($source), imagesy($source));
        
        // 저장
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumb, $fullThumbPath, 85);
                break;
            case 'image/png':
                imagepng($thumb, $fullThumbPath, 8);
                break;
            case 'image/gif':
                imagegif($thumb, $fullThumbPath);
                break;
            case 'image/webp':
                imagewebp($thumb, $fullThumbPath, 85);
                break;
        }
        
        imagedestroy($source);
        imagedestroy($thumb);
        
        return [
            'filepath' => $thumbPath,
            'url' => '/public/uploads/' . $thumbPath
        ];
    }
    
    /**
     * 파일 크기 포맷
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
