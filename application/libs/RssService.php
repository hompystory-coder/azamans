<?php
/**
 * RSS Service
 * RSS 피드 생성 및 관리 서비스
 */

class RssService {
    
    private static $baseUrl;
    private static $db;
    private static $siteName;
    private static $siteDescription;
    
    /**
     * 초기화
     */
    private static function init() {
        if (!self::$baseUrl) {
            self::$baseUrl = defined('ROOTURL') ? ROOTURL : rtrim(env('APP_URL', getConfig('site_url', '')), '/');
        }
        if (!self::$db) {
            self::$db = getDBConnection();
        }
        if (!self::$siteName) {
            self::$siteName = getConfig('site_name', 'MVC Framework');
        }
        if (!self::$siteDescription) {
            self::$siteDescription = getConfig('seo_description', 'PHP MVC Framework로 구축된 웹사이트');
        }
    }
    
    /**
     * RSS Index 생성 (RSS Sitemap)
     * 
     * @return string XML content
     */
    public static function buildIndex() {
        self::init();
        
        $baseUrl = self::$baseUrl;
        $today = date('c'); // ISO 8601 format
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/rss.xsl"?>' . "\n";
        $xml .= '<rssindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n\n";
        
        // BBS RSS 활성화 체크
        if (getConfig('rss_bbs_enabled', 'Y') === 'Y') {
            $xml .= "  <rss>\n";
            $xml .= "    <loc>{$baseUrl}/rss_bbs.xml</loc>\n";
            $xml .= "    <lastmod>{$today}</lastmod>\n";
            $xml .= "    <title>게시판 RSS</title>\n";
            $xml .= "  </rss>\n\n";
        }
        
        // News RSS 활성화 체크
        if (getConfig('rss_news_enabled', 'Y') === 'Y') {
            $xml .= "  <rss>\n";
            $xml .= "    <loc>{$baseUrl}/rss_news.xml</loc>\n";
            $xml .= "    <lastmod>{$today}</lastmod>\n";
            $xml .= "    <title>뉴스 RSS</title>\n";
            $xml .= "  </rss>\n\n";
        }
        
        $xml .= '</rssindex>';
        
        return $xml;
    }
    
    /**
     * 게시판 RSS 생성
     * 
     * @param int $limit 최대 항목 개수
     * @param int $days 추출 기간 (일)
     * @return string RSS XML content
     */
    public static function buildBbs($limit = null, $days = null) {
        self::init();
        
        if ($limit === null) {
            $limit = (int) getConfig('rss_item_limit', 100);
        }
        if ($days === null) {
            $days = (int) getConfig('rss_extract_days', 30);
        }
        
        $baseUrl = self::$baseUrl;
        $siteName = self::$siteName;
        
        // RSS 2.0 헤더
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/rss.xsl"?>' . "\n";
        $xml .= '<rss version="2.0" ';
        $xml .= 'xmlns:atom="http://www.w3.org/2005/Atom" ';
        $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
        $xml .= 'xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        
        $xml .= "  <channel>\n";
        $xml .= "    <title>" . htmlspecialchars($siteName . ' - 게시판') . "</title>\n";
        $xml .= "    <link>{$baseUrl}</link>\n";
        $xml .= "    <description>" . htmlspecialchars(self::$siteDescription) . "</description>\n";
        $xml .= "    <language>ko-KR</language>\n";
        $xml .= "    <lastBuildDate>" . date(DATE_RSS) . "</lastBuildDate>\n";
        
        // Atom self link
        $xml .= "    <atom:link href=\"{$baseUrl}/rss_bbs.xml\" rel=\"self\" type=\"application/rss+xml\" />\n\n";
        
        // 게시판 데이터 조회
        try {
            $dateLimit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            // 활성화된 게시판 목록 가져오기
            $bbsConfig = getConfig('rss_bbs_list', '');
            $bbsIds = $bbsConfig ? explode(',', $bbsConfig) : [];
            
            if (empty($bbsIds)) {
                // 설정이 없으면 모든 게시판
                $bbsCondition = "1=1";
            } else {
                $bbsIdsEscaped = array_map(function($id) {
                    return "'" . addslashes(trim($id)) . "'";
                }, $bbsIds);
                $bbsCondition = "bbs_id IN (" . implode(',', $bbsIdsEscaped) . ")";
            }
            
            $query = "
                SELECT 
                    uid,
                    bbs_id,
                    subject,
                    content,
                    name,
                    reg_date,
                    hit
                FROM bbs_data
                WHERE {$bbsCondition}
                    AND del = 'N'
                    AND secret = 'N'
                    AND block = 'N'
                    AND reg_date >= '{$dateLimit}'
                ORDER BY reg_date DESC
                LIMIT {$limit}
            ";
            
            $posts = getDbArray($query);
            
            foreach ($posts as $post) {
                $postUrl = $baseUrl . '/bbs/' . urlencode($post['bbs_id']) . '/view/' . $post['uid'];
                $pubDate = date(DATE_RSS, strtotime($post['reg_date']));
                
                // 본문에서 이미지 추출
                $content = $post['content'];
                $description = htmlspecialchars(mb_substr(strip_tags($content), 0, 200));
                
                $xml .= "    <item>\n";
                $xml .= "      <title>" . htmlspecialchars($post['subject']) . "</title>\n";
                $xml .= "      <link>{$postUrl}</link>\n";
                $xml .= "      <guid isPermaLink=\"true\">{$postUrl}</guid>\n";
                $xml .= "      <pubDate>{$pubDate}</pubDate>\n";
                $xml .= "      <dc:creator>" . htmlspecialchars($post['name']) . "</dc:creator>\n";
                $xml .= "      <category>" . htmlspecialchars($post['bbs_id']) . "</category>\n";
                $xml .= "      <description>{$description}</description>\n";
                
                // 전체 본문 (CDATA)
                $xml .= "      <content:encoded><![CDATA[{$content}]]></content:encoded>\n";
                $xml .= "    </item>\n\n";
            }
            
        } catch (Exception $e) {
            error_log("RSS BBS generation error: " . $e->getMessage());
        }
        
        $xml .= "  </channel>\n";
        $xml .= "</rss>";
        
        return $xml;
    }
    
    /**
     * 뉴스 RSS 생성
     * 
     * @param int $limit 최대 항목 개수
     * @param int $days 추출 기간 (일)
     * @return string RSS XML content
     */
    public static function buildNews($limit = null, $days = null) {
        self::init();
        
        if ($limit === null) {
            $limit = (int) getConfig('rss_item_limit', 100);
        }
        if ($days === null) {
            $days = (int) getConfig('rss_extract_days', 30);
        }
        
        $baseUrl = self::$baseUrl;
        $siteName = self::$siteName;
        
        // RSS 2.0 헤더
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/rss.xsl"?>' . "\n";
        $xml .= '<rss version="2.0" ';
        $xml .= 'xmlns:atom="http://www.w3.org/2005/Atom" ';
        $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
        $xml .= 'xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        
        $xml .= "  <channel>\n";
        $xml .= "    <title>" . htmlspecialchars($siteName . ' - 뉴스') . "</title>\n";
        $xml .= "    <link>{$baseUrl}</link>\n";
        $xml .= "    <description>" . htmlspecialchars(self::$siteDescription) . "</description>\n";
        $xml .= "    <language>ko-KR</language>\n";
        $xml .= "    <lastBuildDate>" . date(DATE_RSS) . "</lastBuildDate>\n";
        
        // Atom self link
        $xml .= "    <atom:link href=\"{$baseUrl}/rss_news.xml\" rel=\"self\" type=\"application/rss+xml\" />\n\n";
        
        // 뉴스 데이터 조회
        try {
            $dateLimit = date('Y-m-d H:i:s', strtotime("-{$days} days"));
            
            // 활성화된 뉴스 카테고리 목록 가져오기
            $newsConfig = getConfig('rss_news_list', '');
            $newsIds = $newsConfig ? explode(',', $newsConfig) : [];
            
            if (empty($newsIds)) {
                // 설정이 없으면 모든 뉴스
                $newsCondition = "1=1";
            } else {
                $newsIdsEscaped = array_map(function($id) {
                    return "'" . addslashes(trim($id)) . "'";
                }, $newsIds);
                $newsCondition = "i.news_id IN (" . implode(',', $newsIdsEscaped) . ")";
            }
            
            $query = "
                SELECT 
                    i.uid,
                    i.news_id,
                    i.category,
                    d.subject,
                    d.content,
                    d.name,
                    i.reg_date,
                    i.hit
                FROM news_index i
                JOIN news_data d ON i.uid = d.uid
                WHERE {$newsCondition}
                    AND i.del = 'N'
                    AND i.secret = 'N'
                    AND i.block = 'N'
                    AND i.reg_date >= '{$dateLimit}'
                ORDER BY i.reg_date DESC
                LIMIT {$limit}
            ";
            
            $news = getDbArray($query);
            
            foreach ($news as $item) {
                $newsUrl = $baseUrl . '/news/' . urlencode($item['news_id']) . '/view/' . $item['uid'];
                $pubDate = date(DATE_RSS, strtotime($item['reg_date']));
                
                // 본문에서 이미지 추출
                $content = $item['content'];
                $description = htmlspecialchars(mb_substr(strip_tags($content), 0, 200));
                
                $xml .= "    <item>\n";
                $xml .= "      <title>" . htmlspecialchars($item['subject']) . "</title>\n";
                $xml .= "      <link>{$newsUrl}</link>\n";
                $xml .= "      <guid isPermaLink=\"true\">{$newsUrl}</guid>\n";
                $xml .= "      <pubDate>{$pubDate}</pubDate>\n";
                $xml .= "      <dc:creator>" . htmlspecialchars($item['name']) . "</dc:creator>\n";
                
                if (!empty($item['category'])) {
                    $xml .= "      <category>" . htmlspecialchars($item['category']) . "</category>\n";
                }
                
                $xml .= "      <description>{$description}</description>\n";
                
                // 전체 본문 (CDATA)
                $xml .= "      <content:encoded><![CDATA[{$content}]]></content:encoded>\n";
                $xml .= "    </item>\n\n";
            }
            
        } catch (Exception $e) {
            error_log("RSS News generation error: " . $e->getMessage());
        }
        
        $xml .= "  </channel>\n";
        $xml .= "</rss>";
        
        return $xml;
    }
    
    /**
     * 모든 RSS 피드 생성
     * 
     * @return array 생성 결과
     */
    public static function generateAll() {
        self::init();  // 초기화 추가
        
        $results = [];
        
        try {
            // RSS Index 생성
            $indexXml = self::buildIndex();
            $indexFile = ROOTPATH . '/rss_index.xml';  // public 제거
            file_put_contents($indexFile, $indexXml);
            $results['index'] = ['success' => true, 'file' => 'rss_index.xml'];
        } catch (Exception $e) {
            $results['index'] = ['success' => false, 'error' => $e->getMessage()];
        }
        
        try {
            // BBS RSS 생성
            if (getConfig('rss_bbs_enabled', 'Y') === 'Y') {
                $bbsXml = self::buildBbs();
                $bbsFile = ROOTPATH . '/rss_bbs.xml';  // public 제거
                file_put_contents($bbsFile, $bbsXml);
                $results['bbs'] = ['success' => true, 'file' => 'rss_bbs.xml'];
            } else {
                $results['bbs'] = ['success' => false, 'error' => 'BBS RSS disabled'];
            }
        } catch (Exception $e) {
            $results['bbs'] = ['success' => false, 'error' => $e->getMessage()];
        }
        
        try {
            // News RSS 생성
            if (getConfig('rss_news_enabled', 'Y') === 'Y') {
                $newsXml = self::buildNews();
                $newsFile = ROOTPATH . '/rss_news.xml';  // public 제거
                file_put_contents($newsFile, $newsXml);
                $results['news'] = ['success' => true, 'file' => 'rss_news.xml'];
            } else {
                $results['news'] = ['success' => false, 'error' => 'News RSS disabled'];
            }
        } catch (Exception $e) {
            $results['news'] = ['success' => false, 'error' => $e->getMessage()];
        }
        
        return $results;
    }
    
    /**
     * 캐시 가져오기
     * 
     * @param string $key 캐시 키
     * @return string|false 캐시된 데이터 또는 false
     */
    private static function getCache($key) {
        $cacheFile = ROOTPATH . '/cache/rss_' . $key . '.cache';
        
        if (file_exists($cacheFile)) {
            $data = unserialize(file_get_contents($cacheFile));
            if ($data['expire'] > time()) {
                return $data['content'];
            }
        }
        
        return false;
    }
    
    /**
     * 캐시 저장
     * 
     * @param string $key 캐시 키
     * @param string $content 캐시할 내용
     * @param int $ttl 만료 시간 (초)
     */
    private static function setCache($key, $content, $ttl = 3600) {
        $cacheDir = ROOTPATH . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . '/rss_' . $key . '.cache';
        $data = [
            'expire' => time() + $ttl,
            'content' => $content
        ];
        
        file_put_contents($cacheFile, serialize($data));
    }
}
