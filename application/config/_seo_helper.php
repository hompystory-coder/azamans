<?php
/**
 * SEO Meta Helper Functions
 * 페이지별 동적 메타 태그 생성
 */

/**
 * 기본 SEO 설정 가져오기
 */
function getDefaultSeoData() {
    static $seoData = null;
    
    if ($seoData === null) {
        // 기본 이미지 경로 가져오기
        $seoImage = getConfig('seo_image', '/public/images/og-default.jpg');
        
        // 상대 경로를 절대 경로로 변환
        if (strpos($seoImage, 'http') !== 0) {
            $seoImage = ROOTURL . $seoImage;
        }
        
        $seoData = [
            'title' => getConfig('seo_title', getConfig('site_name', 'MVC Framework')),
            'description' => getConfig('seo_description', 'PHP MVC Framework'),
            'keywords' => getConfig('seo_keywords', 'PHP, MVC, Framework'),
            'author' => getConfig('seo_author', 'Admin'),
            'image' => $seoImage,
            'url' => getCurrentUrl(),
            'site_name' => getConfig('site_name', 'MVC Framework'),
            'twitter_handle' => getConfig('seo_twitter_handle', '@YourTwitter'),
            'favicon_ico' => getConfig('favicon_ico', '/favicon.ico'),
            'favicon_apple' => getConfig('favicon_apple', '/apple-touch-icon.png')
        ];
    }
    
    return $seoData;
}

/**
 * 게시판 글 보기 페이지의 SEO 데이터 생성
 */
function getBbsSeoData($bbsId, $postId) {
    $defaultSeo = getDefaultSeoData();
    
    // 게시글 정보 조회
    $post = getUidData("
        SELECT title, content, reg_date, member_id 
        FROM bbs_{$bbsId} 
        WHERE uid = ?
    ", [$postId]);
    
    if (!$post) {
        return $defaultSeo;
    }
    
    // 본문에서 첫 번째 이미지 추출
    $image = extractFirstImage($post['content']) ?: $defaultSeo['image'];
    
    // 본문에서 설명 추출 (HTML 태그 제거 후 150자)
    $description = mb_substr(strip_tags($post['content']), 0, 150, 'UTF-8');
    if (strlen($description) >= 150) {
        $description .= '...';
    }
    
    return [
        'title' => $post['title'] ?? $defaultSeo['title'],
        'description' => $description ?: $defaultSeo['description'],
        'keywords' => $defaultSeo['keywords'],
        'author' => $post['member_id'] ?? $defaultSeo['author'],
        'image' => $image,
        'url' => getCurrentUrl(),
        'site_name' => $defaultSeo['site_name'],
        'twitter_handle' => $defaultSeo['twitter_handle'],
        'favicon_ico' => $defaultSeo['favicon_ico'],
        'favicon_apple' => $defaultSeo['favicon_apple']
    ];
}

/**
 * 뉴스 글 보기 페이지의 SEO 데이터 생성
 */
function getNewsSeoData($newsId, $postId) {
    $defaultSeo = getDefaultSeoData();
    
    // 뉴스 글 정보 조회
    $post = getUidData("
        SELECT title, content, reg_date, member_id 
        FROM news_{$newsId} 
        WHERE uid = ?
    ", [$postId]);
    
    if (!$post) {
        return $defaultSeo;
    }
    
    // 본문에서 첫 번째 이미지 추출
    $image = extractFirstImage($post['content']) ?: $defaultSeo['image'];
    
    // 본문에서 설명 추출
    $description = mb_substr(strip_tags($post['content']), 0, 150, 'UTF-8');
    if (strlen($description) >= 150) {
        $description .= '...';
    }
    
    return [
        'title' => $post['title'] ?? $defaultSeo['title'],
        'description' => $description ?: $defaultSeo['description'],
        'keywords' => $defaultSeo['keywords'],
        'author' => $post['member_id'] ?? $defaultSeo['author'],
        'image' => $image,
        'url' => getCurrentUrl(),
        'site_name' => $defaultSeo['site_name'],
        'twitter_handle' => $defaultSeo['twitter_handle'],
        'favicon_ico' => $defaultSeo['favicon_ico'],
        'favicon_apple' => $defaultSeo['favicon_apple']
    ];
}

/**
 * 페이지 메뉴의 SEO 데이터 생성
 */
function getPageSeoData($menuTable, $menuId) {
    $defaultSeo = getDefaultSeoData();
    
    // 메뉴 정보 조회
    $tableName = $menuTable . '_menu';
    $menu = getUidData("SELECT * FROM {$tableName} WHERE id = ?", [$menuId]);
    
    if (!$menu) {
        return $defaultSeo;
    }
    
    // 페이지 콘텐츠 조회
    $page = getUidData("
        SELECT content 
        FROM menu_pages 
        WHERE menu_id = ? AND menu_table = ?
    ", [$menuId, $menuTable]);
    
    $content = $page['content'] ?? '';
    
    // 본문에서 첫 번째 이미지 추출
    $image = extractFirstImage($content) ?: $defaultSeo['image'];
    
    // 본문에서 설명 추출
    $description = mb_substr(strip_tags($content), 0, 150, 'UTF-8');
    if (strlen($description) >= 150) {
        $description .= '...';
    }
    
    return [
        'title' => $menu['menu_name'] ?? $defaultSeo['title'],
        'description' => $description ?: $defaultSeo['description'],
        'keywords' => $defaultSeo['keywords'],
        'author' => $defaultSeo['author'],
        'image' => $image,
        'url' => getCurrentUrl(),
        'site_name' => $defaultSeo['site_name'],
        'twitter_handle' => $defaultSeo['twitter_handle'],
        'favicon_ico' => $defaultSeo['favicon_ico'],
        'favicon_apple' => $defaultSeo['favicon_apple']
    ];
}

/**
 * HTML 콘텐츠에서 첫 번째 이미지 URL 추출
 */
function extractFirstImage($html) {
    if (empty($html)) {
        return null;
    }
    
    // <img> 태그에서 src 추출
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
        $imgSrc = $matches[1];
        
        // 상대 경로를 절대 경로로 변환
        if (strpos($imgSrc, 'http') !== 0) {
            if (strpos($imgSrc, '/') === 0) {
                return ROOTURL . $imgSrc;
            } else {
                return ROOTURL . '/' . $imgSrc;
            }
        }
        
        return $imgSrc;
    }
    
    return null;
}

/**
 * SEO 메타 데이터 자동 감지 및 반환
 * 현재 페이지 타입에 따라 적절한 SEO 데이터 반환
 */
function getPageSeoMetaData() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    
    // 1. 게시판 상세 페이지: /bbs/{bbsId}/view/{postId}
    if (preg_match('#^/bbs/([^/]+)/view/(\d+)#', $uri, $matches)) {
        return getBbsSeoData($matches[1], $matches[2]);
    }
    
    // 2. 뉴스 상세 페이지: /news/{newsId}/view/{postId}
    if (preg_match('#^/news/([^/]+)/view/(\d+)#', $uri, $matches)) {
        return getNewsSeoData($matches[1], $matches[2]);
    }
    
    // 3. 페이지 메뉴: /page/header/{id} 또는 /page/footer/{id}
    if (preg_match('#^/page/(header|footer)/(\d+)#', $uri, $matches)) {
        return getPageSeoData($matches[1], $matches[2]);
    }
    
    // 4. 기본 SEO 데이터
    return getDefaultSeoData();
}
