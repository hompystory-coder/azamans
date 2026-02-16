<?php
/**
 * Admin SEO Functions
 * 관리자 SEO 설정 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * SEO 설정 핸들러
 */
function admin_seo_handler($controller, $action = null) {
    // 설정 저장
    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $metaTitle = $_POST['meta_title'] ?? '';
        $metaDescription = $_POST['meta_description'] ?? '';
        $metaKeywords = $_POST['meta_keywords'] ?? '';
        $ogTitle = $_POST['og_title'] ?? '';
        $ogDescription = $_POST['og_description'] ?? '';
        $ogImage = $_POST['og_image'] ?? '';
        $twitterCard = $_POST['twitter_card'] ?? 'summary';
        
        setConfig('seo_meta_title', $metaTitle);
        setConfig('seo_meta_description', $metaDescription);
        setConfig('seo_meta_keywords', $metaKeywords);
        setConfig('seo_og_title', $ogTitle);
        setConfig('seo_og_description', $ogDescription);
        setConfig('seo_og_image', $ogImage);
        setConfig('seo_twitter_card', $twitterCard);
        
        $controller->renderJson(['success' => true, 'message' => 'SEO 설정이 저장되었습니다.']);
        return;
    }
    
    // SEO 설정 페이지
    $data = [
        'title' => 'SEO 설정',
        'meta_title' => getConfig('seo_meta_title', ''),
        'meta_description' => getConfig('seo_meta_description', ''),
        'meta_keywords' => getConfig('seo_meta_keywords', ''),
        'og_title' => getConfig('seo_og_title', ''),
        'og_description' => getConfig('seo_og_description', ''),
        'og_image' => getConfig('seo_og_image', ''),
        'twitter_card' => getConfig('seo_twitter_card', 'summary')
    ];
    $controller->renderView('admin/seo', $data);
}
