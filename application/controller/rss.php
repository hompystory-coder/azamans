<?php
/**
 * RSS Controller
 * RSS 피드 생성 및 제공
 */

class Rss extends Controller {
    
    /**
     * RSS Index (RSS Sitemap)
     * URL: /rss_index.xml
     */
    public function index() {
        try {
            $xml = RssService::buildIndex();
            
            header('Content-Type: application/xml; charset=UTF-8');
            echo $xml;
            exit;
            
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'RSS Index generation failed: ' . $e->getMessage();
            exit;
        }
    }
    
    /**
     * 게시판 RSS 피드
     * URL: /rss_bbs.xml
     */
    public function bbs() {
        try {
            // RSS 활성화 체크
            if (getConfig('rss_bbs_enabled', 'Y') !== 'Y') {
                header('HTTP/1.1 404 Not Found');
                echo 'BBS RSS is disabled';
                exit;
            }
            
            $xml = RssService::buildBbs();
            
            header('Content-Type: application/rss+xml; charset=UTF-8');
            header('X-Robots-Tag: all');
            echo $xml;
            exit;
            
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'BBS RSS generation failed: ' . $e->getMessage();
            exit;
        }
    }
    
    /**
     * 뉴스 RSS 피드
     * URL: /rss_news.xml
     */
    public function news() {
        try {
            // RSS 활성화 체크
            if (getConfig('rss_news_enabled', 'Y') !== 'Y') {
                header('HTTP/1.1 404 Not Found');
                echo 'News RSS is disabled';
                exit;
            }
            
            $xml = RssService::buildNews();
            
            header('Content-Type: application/rss+xml; charset=UTF-8');
            header('X-Robots-Tag: all');
            echo $xml;
            exit;
            
        } catch (Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'News RSS generation failed: ' . $e->getMessage();
            exit;
        }
    }
}
