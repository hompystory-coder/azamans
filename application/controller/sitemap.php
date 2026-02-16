<?php
/**
 * Sitemap Controller
 * 사이트맵 컨트롤러
 * 
 * URL 라우팅:
 * - /sitemap_index.xml       -> index()
 * - /sitemap_news.xml        -> news()
 * - /sitemap_bbs.xml         -> bbs()
 * - /sitemap_{type}_{year}_{month}.xml -> monthly()
 */

// SitemapService 로드
require_once __DIR__ . '/../libs/SitemapService.php';

class Sitemap extends Controller {
    
    /**
     * Sitemap Index
     * URL: /sitemap_index.xml
     */
    public function index() {
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        
        echo SitemapService::buildIndex();
        exit;
    }
    
    /**
     * News Sitemap
     * URL: /sitemap_news.xml
     */
    public function news() {
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        
        echo SitemapService::buildNews();
        exit;
    }
    
    /**
     * BBS Sitemap
     * URL: /sitemap_bbs.xml
     */
    public function bbs() {
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        
        echo SitemapService::buildBbs();
        exit;
    }
    
    /**
     * Monthly Archive Sitemap
     * URL: /sitemap_{type}_{year}_{month}.xml
     * 
     * @param string $type 'news' or 'bbs'
     * @param string $year 4자리 연도
     * @param string $month 2자리 월
     */
    public function monthly($type = 'news', $year = null, $month = null) {
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');
        
        // URL에서 파라미터 추출
        // 예: /sitemap_news_2026_02.xml
        if (!$year || !$month) {
            $year = date('Y');
            $month = date('m');
        }
        
        $yearMonth = sprintf('%s_%02d', $year, intval($month));
        
        // type 검증
        if (!in_array($type, ['news', 'bbs'])) {
            $type = 'news';
        }
        
        echo SitemapService::buildMonthly($type, $yearMonth);
        exit;
    }
}
