<?php
/**
 * MVC Framework Entry Point
 * 모든 요청의 진입점
 */

// 기본 경로 설정
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/application');
define('PUBLIC_PATH', BASE_PATH . '/public');

// 환경설정 로드
require_once APP_PATH . '/config/_env.func.php';
loadEnv(BASE_PATH . '/.env');

// 에러 리포팅 설정
if (isDebug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 타임존 설정
date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Seoul'));

// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', env('SESSION_LIFETIME', 7200));
    session_start();
}

// 설정 파일 로드
require_once APP_PATH . '/config/_db_info.php';
require_once APP_PATH . '/config/_db_func.php';
require_once APP_PATH . '/config/_sys.func.php';
require_once APP_PATH . '/config/_security.func.php';
require_once APP_PATH . '/config/_pagination.func.php';

// MVC 라이브러리 로드
require_once APP_PATH . '/libs/controller.php';
require_once APP_PATH . '/libs/application.php';

// 애플리케이션 시작
$app = new Application();
