<?php
/**
 * Sitemap 캐시 무효화
 * 게시물/뉴스 작성 시 호출
 * 
 * @param string $type 'bbs' or 'news'
 * @return bool
 */
function invalidateSitemapCache($type = 'bbs') {
    try {
        $cacheDir = APP_PATH . '/cache/sitemap/';
        
        // 캐시 디렉토리가 없으면 생성
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        // 해당 타입의 캐시 파일 삭제
        $cacheFile = $cacheDir . 'sitemap_' . $type . '.cache';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        
        // index 캐시도 삭제
        $indexCache = $cacheDir . 'sitemap_index.cache';
        if (file_exists($indexCache)) {
            unlink($indexCache);
        }
        
        return true;
    } catch (Exception $e) {
        error_log('Sitemap cache invalidation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Sitemap 자동 업데이트 (레거시 함수, 호환성 유지)
 * 
 * @deprecated 캐싱 시스템을 사용하므로 invalidateSitemapCache() 사용 권장
 */
function updateSitemap($type = 'bbs') {
    return invalidateSitemapCache($type);
}

/**
 * JSON 응답 출력
 * 
 * @param array $data 응답 데이터
 * @param int $statusCode HTTP 상태 코드
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
