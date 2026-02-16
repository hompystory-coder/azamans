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

// jsonResponse() 함수는 /application/config/_sys.func.php에 정의되어 있습니다.
