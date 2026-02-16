<?php
/**
 * Sitemap Service
 * 사이트맵 생성 및 관리 서비스
 */

class SitemapService {
    
    private static $baseUrl;
    private static $db;
    
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
    }
    
    /**
     * Sitemap Index 생성
     * 
     * @return string XML content
     */
    public static function buildIndex() {
        self::init();
        
        // 캐시 확인
        $cached = self::getCache('index');
        if ($cached !== false) {
            return $cached;
        }
        
        $today = date('Y-m-d');
        $baseUrl = self::$baseUrl;
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/sitemap.xsl"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n\n";
        
        // News Sitemap
        $xml .= "  <sitemap>\n";
        $xml .= "    <loc>{$baseUrl}/sitemap_news.xml</loc>\n";
        $xml .= "    <lastmod>{$today}</lastmod>\n";
        $xml .= "  </sitemap>\n\n";
        
        // BBS Sitemap
        $xml .= "  <sitemap>\n";
        $xml .= "    <loc>{$baseUrl}/sitemap_bbs.xml</loc>\n";
        $xml .= "    <lastmod>{$today}</lastmod>\n";
        $xml .= "  </sitemap>\n\n";
        
        // 확장 가능: 월별 사이트맵 자동 추가
        $monthlySitemaps = self::getMonthlyArchives();
        foreach ($monthlySitemaps as $sitemap) {
            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>{$baseUrl}/{$sitemap['filename']}</loc>\n";
            $xml .= "    <lastmod>{$sitemap['lastmod']}</lastmod>\n";
            $xml .= "  </sitemap>\n\n";
        }
        
        $xml .= '</sitemapindex>';
        
        // 캐시 저장 (1시간)
        self::setCache('index', $xml, 3600);
        
        return $xml;
    }
    
    /**
     * News Sitemap 생성
     * 
     * @param int $limit 최대 URL 개수
     * @return string XML content
     */
    public static function buildNews($limit = 50000) {
        self::init();
        
        // 캐시 확인
        $cached = self::getCache('news');
        if ($cached !== false) {
            return $cached;
        }
        
        $baseUrl = self::$baseUrl;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/sitemap.xsl"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n\n";
        
        try {
            // 제외할 뉴스 ID 목록 가져오기
            $excludedBoards = self::getExcludedBoards('news');
            $excludeCondition = '';
            if (!empty($excludedBoards)) {
                $excludeIds = array_map(function($id) {
                    return "'" . addslashes($id) . "'";
                }, $excludedBoards);
                $excludeCondition = " AND nd.bbs_id NOT IN (" . implode(',', $excludeIds) . ")";
            }
            
            // News 데이터 가져오기
            $query = "
                SELECT 
                    nd.uid,
                    nd.bbs_id,
                    COALESCE(nd.update_date, nd.reg_date) as last_modified
                FROM news_data nd
                WHERE 1=1
                {$excludeCondition}
                ORDER BY nd.uid DESC
                LIMIT {$limit}
            ";
            
            $result = self::$db->query($query);
            
            if ($result) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $loc = "{$baseUrl}/news/{$row['bbs_id']}/view/{$row['uid']}";
                    $lastmod = date('Y-m-d', strtotime($row['last_modified']));
                    
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
                    $xml .= "    <changefreq>hourly</changefreq>\n";
                    $xml .= "    <priority>0.9</priority>\n";
                    $xml .= "  </url>\n\n";
                }
                // PDO: free() 불필요 (자동 메모리 해제)
            }
            
        } catch (Exception $e) {
            error_log("Sitemap News Error: " . $e->getMessage());
        }
        
        $xml .= '</urlset>';
        
        // 캐시 저장 (1시간)
        self::setCache('news', $xml, 3600);
        
        return $xml;
    }
    
    /**
     * BBS Sitemap 생성
     * 
     * @param int $limit 최대 URL 개수
     * @return string XML content
     */
    public static function buildBbs($limit = 50000) {
        self::init();
        
        // 캐시 확인
        $cached = self::getCache('bbs');
        if ($cached !== false) {
            return $cached;
        }
        
        $baseUrl = self::$baseUrl;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/sitemap.xsl"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n\n";
        
        try {
            // 제외할 게시판 ID 목록 가져오기
            $excludedBoards = self::getExcludedBoards('bbs');
            $excludeCondition = '';
            if (!empty($excludedBoards)) {
                $excludeIds = array_map(function($id) {
                    return "'" . addslashes($id) . "'";
                }, $excludedBoards);
                $excludeCondition = " AND bd.bbs_id NOT IN (" . implode(',', $excludeIds) . ")";
            }
            
            // BBS 데이터 가져오기
            $query = "
                SELECT 
                    bd.uid,
                    bd.bbs_id,
                    COALESCE(bd.update_date, bd.reg_date) as last_modified
                FROM bbs_data bd
                WHERE bd.is_secret = 'N'
                {$excludeCondition}
                ORDER BY bd.uid DESC
                LIMIT {$limit}
            ";
            
            $result = self::$db->query($query);
            
            if ($result) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $loc = "{$baseUrl}/bbs/{$row['bbs_id']}/view/{$row['uid']}";
                    $lastmod = date('Y-m-d', strtotime($row['last_modified']));
                    
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
                    $xml .= "    <changefreq>daily</changefreq>\n";
                    $xml .= "    <priority>0.6</priority>\n";
                    $xml .= "  </url>\n\n";
                }
                // PDO: free() 불필요 (자동 메모리 해제)
            }
            
        } catch (Exception $e) {
            error_log("Sitemap BBS Error: " . $e->getMessage());
        }
        
        $xml .= '</urlset>';
        
        // 캐시 저장 (1시간)
        self::setCache('bbs', $xml, 3600);
        
        return $xml;
    }
    
    /**
     * 월별 아카이브 사이트맵 목록 가져오기
     * (향후 확장용)
     * 
     * @return array
     */
    private static function getMonthlyArchives() {
        $archives = [];
        
        try {
            // News 월별 데이터 조회 (최근 12개월)
            $query = "
                SELECT 
                    DATE_FORMAT(reg_date, '%Y') as year,
                    DATE_FORMAT(reg_date, '%m') as month,
                    COUNT(*) as cnt,
                    MAX(COALESCE(update_date, reg_date)) as last_modified
                FROM news_data
                WHERE reg_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY year, month
                HAVING cnt > 0
                ORDER BY year DESC, month DESC
            ";
            
            $result = self::$db->query($query);
            if ($result) {
                $newsCount = 0;
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $yearMonth = $row['year'] . '_' . $row['month'];
                    $archives[] = [
                        'filename' => "sitemap_news_{$yearMonth}.xml",
                        'lastmod' => date('Y-m-d', strtotime($row['last_modified'])),
                        'type' => 'news',
                        'count' => $row['cnt']
                    ];
                    $newsCount++;
                }
                error_log("Monthly Archives: Found {$newsCount} news months");
            }
            
            // BBS 월별 데이터 조회 (최근 12개월)
            $query = "
                SELECT 
                    DATE_FORMAT(reg_date, '%Y') as year,
                    DATE_FORMAT(reg_date, '%m') as month,
                    COUNT(*) as cnt,
                    MAX(COALESCE(update_date, reg_date)) as last_modified
                FROM bbs_data
                WHERE reg_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY year, month
                HAVING cnt > 0
                ORDER BY year DESC, month DESC
            ";
            
            $result = self::$db->query($query);
            if ($result) {
                $bbsCount = 0;
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $yearMonth = $row['year'] . '_' . $row['month'];
                    $archives[] = [
                        'filename' => "sitemap_bbs_{$yearMonth}.xml",
                        'lastmod' => date('Y-m-d', strtotime($row['last_modified'])),
                        'type' => 'bbs',
                        'count' => $row['cnt']
                    ];
                    $bbsCount++;
                }
                error_log("Monthly Archives: Found {$bbsCount} bbs months");
            }
            
        } catch (Exception $e) {
            error_log("Get Monthly Archives Error: " . $e->getMessage());
        }
        
        return $archives;
    }
    
    /**
     * 제외할 게시판/뉴스 ID 목록 가져오기
     * 
     * @param string $type 'bbs' or 'news'
     * @return array
     */
    private static function getExcludedBoards($type = 'bbs') {
        try {
            $configKey = "sitemap_exclude_{$type}";
            $query = "
                SELECT config_value 
                FROM site_config 
                WHERE config_key = '{$configKey}'
                LIMIT 1
            ";
            
            $result = self::$db->query($query);
            
            if ($result && $row = $result->fetch(PDO::FETCH_ASSOC)) {
                $excluded = json_decode($row['config_value'], true);
                return is_array($excluded) ? $excluded : [];
            }
        } catch (Exception $e) {
            error_log("Get Excluded Boards Error: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * 월별 Sitemap 생성 (대량 데이터 분할용)
     * 
     * @param string $type 'news' or 'bbs'
     * @param string $yearMonth 'YYYY_MM' 형식
     * @return string XML content
     */
    public static function buildMonthly($type = 'news', $yearMonth = null) {
        self::init();
        
        if (!$yearMonth) {
            $yearMonth = date('Y_m');
        }
        
        $baseUrl = self::$baseUrl;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . $baseUrl . '/sitemap.xsl"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n\n";
        
        try {
            $table = ($type === 'news') ? 'news_data' : 'bbs_data';
            $urlPath = ($type === 'news') ? 'news' : 'bbs';
            
            // YYYY_MM 형식을 YYYY-MM으로 변환
            $dateFilter = str_replace('_', '-', $yearMonth);
            
            $query = "
                SELECT 
                    uid,
                    bbs_id,
                    COALESCE(update_date, reg_date) as last_modified
                FROM {$table}
                WHERE DATE_FORMAT(reg_date, '%Y-%m') = '{$dateFilter}'
                ORDER BY uid DESC
                LIMIT 50000
            ";
            
            $result = self::$db->query($query);
            
            if ($result) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $loc = "{$baseUrl}/{$urlPath}/{$row['bbs_id']}/view/{$row['uid']}";
                    $lastmod = date('Y-m-d', strtotime($row['last_modified']));
                    
                    $xml .= "  <url>\n";
                    $xml .= "    <loc>" . htmlspecialchars($loc) . "</loc>\n";
                    $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
                    $xml .= "    <changefreq>monthly</changefreq>\n";
                    $xml .= "    <priority>0.5</priority>\n";
                    $xml .= "  </url>\n\n";
                }
                // PDO: free() 불필요 (자동 메모리 해제)
            }
            
        } catch (Exception $e) {
            error_log("Sitemap Monthly Error: " . $e->getMessage());
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * 모든 Sitemap 생성
     * 
     * @return array 생성 결과
     */
    public static function generateAll() {
        self::init();
        
        $results = [];
        
        try {
            // Sitemap Index 생성
            $indexXml = self::buildIndex();
            $indexFile = BASE_PATH . '/sitemap_index.xml';
            file_put_contents($indexFile, $indexXml);
            $results['index'] = ['success' => true, 'file' => 'sitemap_index.xml'];
            
            // 뉴스 Sitemap 생성
            $newsXml = self::buildNews();
            $newsFile = BASE_PATH . '/sitemap_news.xml';
            file_put_contents($newsFile, $newsXml);
            $results['news'] = ['success' => true, 'file' => 'sitemap_news.xml'];
            
            // 게시판 Sitemap 생성
            $bbsXml = self::buildBbs();
            $bbsFile = BASE_PATH . '/sitemap_bbs.xml';
            file_put_contents($bbsFile, $bbsXml);
            $results['bbs'] = ['success' => true, 'file' => 'sitemap_bbs.xml'];
            
            // 캐시 삭제
            self::clearCache();
            
        } catch (Exception $e) {
            $results['error'] = ['success' => false, 'message' => $e->getMessage()];
        }
        
        return $results;
    }
    
    /**
     * 캐시 삭제
     */
    private static function clearCache() {
        $cacheDir = APP_PATH . '/cache/sitemap/';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '*.cache');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * 캐시 가져오기
     * 
     * @param string $type 'index', 'news', 'bbs'
     * @return string|false
     */
    private static function getCache($type) {
        $cacheDir = APP_PATH . '/cache/sitemap/';
        $cacheFile = $cacheDir . 'sitemap_' . $type . '.cache';
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        // 캐시 만료 확인 (1시간)
        if (time() - filemtime($cacheFile) > 3600) {
            return false;
        }
        
        return file_get_contents($cacheFile);
    }
    
    /**
     * 캐시 저장하기
     * 
     * @param string $type 'index', 'news', 'bbs'
     * @param string $content
     * @param int $ttl TTL in seconds
     * @return bool
     */
    private static function setCache($type, $content, $ttl = 3600) {
        $cacheDir = APP_PATH . '/cache/sitemap/';
        
        // 캐시 디렉토리 생성
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        
        $cacheFile = $cacheDir . 'sitemap_' . $type . '.cache';
        
        return file_put_contents($cacheFile, $content) !== false;
    }
}
