<?php
/**
 * System Functions
 * 자주 사용하는 시스템 함수 모음
 */

/**
 * 현재 URL 가져오기
 * @return string
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * 리다이렉트
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * JSON 응답
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * POST 데이터 가져오기
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getPost($key, $default = '') {
    return $_POST[$key] ?? $default;
}

/**
 * GET 데이터 가져오기
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getGet($key, $default = '') {
    return $_GET[$key] ?? $default;
}

/**
 * REQUEST 데이터 가져오기
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getRequest($key, $default = '') {
    return $_REQUEST[$key] ?? $default;
}

/**
 * 세션 값 설정
 * @param string $key
 * @param mixed $value
 */
function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * 세션 값 가져오기
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSession($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

/**
 * 세션 값 삭제
 * @param string $key
 */
function deleteSession($key) {
    if (isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

/**
 * 로그인 여부 확인
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * 관리자 여부 확인
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * 날짜 포맷 변환
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * 상대 시간 표시 (예: 5분 전, 3시간 전)
 * @param string $datetime
 * @return string
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return '방금 전';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '분 전';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '시간 전';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . '일 전';
    } else {
        return date('Y-m-d', $timestamp);
    }
}

/**
 * 파일 크기 포맷 (예: 1.5 MB)
 * @param int $bytes
 * @param int $precision
 * @return string
 */
function formatFileSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * 문자열 자르기 (한글 지원)
 * @param string $str
 * @param int $length
 * @param string $suffix
 * @return string
 */
function cutString($str, $length, $suffix = '...') {
    if (mb_strlen($str, 'UTF-8') <= $length) {
        return $str;
    }
    return mb_substr($str, 0, $length, 'UTF-8') . $suffix;
}

/**
 * 페이지네이션 생성
 * @param int $currentPage 현재 페이지
 * @param int $totalPages 전체 페이지 수
 * @param string $baseUrl 기본 URL
 * @param int $range 표시할 페이지 범위
 * @return string HTML
 */
function pagination($currentPage, $totalPages, $baseUrl, $range = 5) {
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination"><ul>';
    
    // 이전 페이지
    if ($currentPage > 1) {
        $html .= '<li><a href="' . $baseUrl . ($currentPage - 1) . '">&laquo; 이전</a></li>';
    }
    
    // 페이지 번호
    $start = max(1, $currentPage - floor($range / 2));
    $end = min($totalPages, $start + $range - 1);
    $start = max(1, $end - $range + 1);
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $currentPage) ? ' class="active"' : '';
        $html .= '<li' . $active . '><a href="' . $baseUrl . $i . '">' . $i . '</a></li>';
    }
    
    // 다음 페이지
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . $baseUrl . ($currentPage + 1) . '">다음 &raquo;</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

/**
 * 알림 메시지 표시
 * @param string $message
 * @param string $type (success|error|warning|info)
 */
function showAlert($message, $type = 'info') {
    echo '<div class="alert alert-' . $type . '">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>';
}

/**
 * 디버그 출력
 * @param mixed $var
 * @param bool $die
 */
function debug($var, $die = false) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
    if ($die) {
        die();
    }
}

/**
 * 현재 도메인 가져오기
 * @return string
 */
function getDomain() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'];
}

/**
 * IP 주소 가져오기
 * @return string
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
}

/**
 * 랜덤 문자열 생성
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * 설정 값 가져오기 (DB에서)
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getConfig($key, $default = null) {
    static $configs = null;
    
    if ($configs === null) {
        $configs = [];
        $rows = getDbArray("SELECT config_key, config_value FROM admin_config");
        foreach ($rows as $row) {
            $configs[$row['config_key']] = $row['config_value'];
        }
    }
    
    return $configs[$key] ?? $default;
}

/**
 * 설정 값 저장 (DB에)
 * @param string $key
 * @param mixed $value
 * @return bool
 */
function setConfig($key, $value) {
    $exists = getUidData("SELECT uid FROM admin_config WHERE config_key = ?", [$key]);
    
    if ($exists) {
        return getDbUpdate('admin_config', 
            ['config_value' => $value, 'updated_at' => date('Y-m-d H:i:s')], 
            'config_key = ?', 
            [$key]
        ) !== false;
    } else {
        return getDbInsert('admin_config', [
            'config_key' => $key,
            'config_value' => $value,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]) !== false;
    }
}

/**
 * 방문자 기록
 */
function trackVisitor() {
    $ip = getClientIP();
    $today = date('Y-m-d');
    
    // 오늘 이미 기록된 IP인지 확인
    $exists = getUidData("SELECT uid FROM visitor_stats WHERE visit_date = ? AND ip_address = ?", [$today, $ip]);
    
    if (!$exists) {
        // 신규 방문자 기록
        getDbInsert('visitor_stats', [
            'visit_date' => $today,
            'ip_address' => $ip,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referer' => $_SERVER['HTTP_REFERER'] ?? '',
            'page_url' => $_SERVER['REQUEST_URI'] ?? ''
        ]);
    }
}
