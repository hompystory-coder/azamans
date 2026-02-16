<?php
/**
 * Security Functions
 * 보안 관련 함수 모음
 */

/**
 * XSS 방지 필터
 * @param string|null $str
 * @return string
 */
function xssFilter($str) {
    if ($str === null) {
        return '';
    }
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * SQL Injection 방지 (추가 보안 레이어)
 * 주의: PDO prepare를 사용하는 것이 우선이며, 이 함수는 추가 보안용
 * @param string $str
 * @return string
 */
function sqlFilter($str) {
    $str = trim($str);
    $str = strip_tags($str);
    $str = addslashes($str);
    return $str;
}

/**
 * 입력 데이터 정리 (XSS + SQL Injection)
 * @param string|array $data
 * @return string|array
 */
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * CSRF 토큰 생성
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF 토큰 검증
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * CSRF 토큰 HTML 필드 생성
 * @return string
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * 비밀번호 해시 생성
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * 비밀번호 검증
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * 안전한 파일 업로드 검증
 * @param array $file $_FILES 배열의 항목
 * @param array $allowedTypes 허용된 MIME 타입
 * @param int $maxSize 최대 파일 크기 (바이트)
 * @return array ['success' => bool, 'message' => string, 'filename' => string]
 */
function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
    // 5MB 기본값
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => '잘못된 파일입니다.'];
    }
    
    // 에러 체크
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => '파일 업로드 중 오류가 발생했습니다.'];
    }
    
    // 파일 크기 체크
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => '파일 크기가 너무 큽니다. (최대: ' . formatFileSize($maxSize) . ')'];
    }
    
    // MIME 타입 체크
    if (!empty($allowedTypes)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => '허용되지 않는 파일 형식입니다.'];
        }
    }
    
    // 안전한 파일명 생성
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeFilename = uniqid('file_', true) . '.' . $extension;
    
    return [
        'success' => true,
        'message' => '파일 검증 성공',
        'filename' => $safeFilename,
        'original_name' => $file['name'],
        'size' => $file['size'],
        'tmp_name' => $file['tmp_name']
    ];
}

/**
 * 이메일 유효성 검사
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * URL 유효성 검사
 * @param string $url
 * @return bool
 */
function validateURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * 전화번호 유효성 검사 (한국)
 * @param string $phone
 * @return bool
 */
function validatePhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    return preg_match('/^(01[016789]|02|0[3-9][0-9])[0-9]{3,4}[0-9]{4}$/', $phone);
}

/**
 * 사용자 입력 검증 (필수 필드 체크)
 * @param array $required 필수 필드 배열
 * @param array $data 입력 데이터
 * @return array ['valid' => bool, 'missing' => array]
 */
function validateRequired($required, $data) {
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    
    return [
        'valid' => empty($missing),
        'missing' => $missing
    ];
}

/**
 * IP 주소 차단 확인
 * @param string $ip
 * @return bool
 */
function isIPBlocked($ip) {
    $blocked = getUidData("SELECT uid FROM ip_blocks WHERE ip_address = ? AND expires_at > NOW()", [$ip]);
    return $blocked !== false;
}

/**
 * IP 주소 차단
 * @param string $ip
 * @param int $minutes 차단 시간 (분)
 * @param string $reason 차단 사유
 * @return bool
 */
function blockIP($ip, $minutes = 60, $reason = '') {
    $expiresAt = date('Y-m-d H:i:s', time() + ($minutes * 60));
    
    return getDbInsert('ip_blocks', [
        'ip_address' => $ip,
        'reason' => $reason,
        'expires_at' => $expiresAt,
        'created_at' => date('Y-m-d H:i:s')
    ]) !== false;
}

/**
 * Rate Limiting (요청 횟수 제한)
 * @param string $key 제한 키 (예: IP 주소, 사용자 ID)
 * @param int $maxAttempts 최대 시도 횟수
 * @param int $decayMinutes 제한 시간 (분)
 * @return array ['allowed' => bool, 'remaining' => int, 'retry_after' => int]
 */
function rateLimit($key, $maxAttempts = 5, $decayMinutes = 1) {
    $cacheKey = 'rate_limit_' . md5($key);
    
    // 세션 기반 간단한 구현 (프로덕션에서는 Redis 등 사용 권장)
    if (!isset($_SESSION[$cacheKey])) {
        $_SESSION[$cacheKey] = [
            'attempts' => 0,
            'reset_at' => time() + ($decayMinutes * 60)
        ];
    }
    
    $data = $_SESSION[$cacheKey];
    
    // 시간 초과 시 리셋
    if (time() >= $data['reset_at']) {
        $_SESSION[$cacheKey] = [
            'attempts' => 1,
            'reset_at' => time() + ($decayMinutes * 60)
        ];
        return ['allowed' => true, 'remaining' => $maxAttempts - 1, 'retry_after' => 0];
    }
    
    // 시도 횟수 증가
    $_SESSION[$cacheKey]['attempts']++;
    $attempts = $_SESSION[$cacheKey]['attempts'];
    
    $allowed = $attempts <= $maxAttempts;
    $remaining = max(0, $maxAttempts - $attempts);
    $retryAfter = $allowed ? 0 : ($data['reset_at'] - time());
    
    return [
        'allowed' => $allowed,
        'remaining' => $remaining,
        'retry_after' => $retryAfter
    ];
}

/**
 * 디렉토리 트래버설 공격 방지
 * @param string $path
 * @param string $basePath
 * @return bool
 */
function isPathSafe($path, $basePath) {
    $realPath = realpath($path);
    $realBasePath = realpath($basePath);
    
    return $realPath !== false && 
           $realBasePath !== false && 
           strpos($realPath, $realBasePath) === 0;
}

/**
 * 로그 기록
 * @param string $message
 * @param string $level (info|warning|error)
 * @param array $context
 */
function securityLog($message, $level = 'info', $context = []) {
    $logFile = __DIR__ . '/../../logs/security_' . date('Y-m-d') . '.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    
    $logMessage = "[{$timestamp}] [{$level}] IP: {$ip} - {$message}";
    if ($contextStr) {
        $logMessage .= " | Context: {$contextStr}";
    }
    $logMessage .= PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
