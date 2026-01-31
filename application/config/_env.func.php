<?php
/**
 * Environment Configuration Helper
 * .env 파일 로더 및 환경변수 관리
 */

/**
 * .env 파일 로드
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // 주석 무시
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // KEY=VALUE 형식 파싱
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // 따옴표 제거
            $value = trim($value, '"\'');
            
            // 환경변수 및 $_ENV에 설정
            if (!array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
    }
    
    return true;
}

/**
 * 환경변수 조회
 */
function env($key, $default = null) {
    $value = getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // boolean 변환
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
    }
    
    return $value;
}

/**
 * 환경변수 설정
 */
function setEnv($key, $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
}

/**
 * 환경변수가 존재하는지 확인
 */
function hasEnv($key) {
    return getenv($key) !== false;
}

/**
 * 앱 환경 확인
 */
function isProduction() {
    return env('APP_ENV') === 'production';
}

function isDevelopment() {
    return env('APP_ENV') === 'development';
}

function isDebug() {
    return env('APP_DEBUG', false) === true;
}

/**
 * 데이터베이스 설정 조회
 */
function dbConfig() {
    return [
        'host' => env('DB_HOST', 'localhost'),
        'name' => env('DB_NAME', 'mvc'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4')
    ];
}

/**
 * 앱 설정 조회
 */
function appConfig() {
    return [
        'name' => env('APP_NAME', 'MVC Framework'),
        'url' => env('APP_URL', 'http://localhost'),
        'env' => env('APP_ENV', 'development'),
        'debug' => env('APP_DEBUG', false),
        'timezone' => env('APP_TIMEZONE', 'Asia/Seoul')
    ];
}

/**
 * 포인트 설정 조회
 */
function pointConfig() {
    return [
        'post_write' => (int)env('POINT_POST_WRITE', 10),
        'comment_write' => (int)env('POINT_COMMENT_WRITE', 5),
        'daily_login' => (int)env('POINT_DAILY_LOGIN', 2)
    ];
}
