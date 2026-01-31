<?php
/**
 * Database Configuration
 * DB 접속 정보 설정 파일
 */

// DB 접속 정보
define('DB_HOST', 'localhost');
define('DB_NAME', 'mvc');
define('DB_USER', 'root');
define('DB_PASS', 'cnNbjf!%jd@&kVgD');
define('DB_CHARSET', 'utf8mb4');

// PDO 옵션
define('PDO_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]);

/**
 * DB 연결 함수
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
        } catch (PDOException $e) {
            die("데이터베이스 연결 실패: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * DB 연결 확인
 * @return bool
 */
function isConnectedToDB() {
    try {
        $pdo = getDBConnection();
        return $pdo !== null;
    } catch (Exception $e) {
        return false;
    }
}
