<?php
/**
 * page_files 테이블 생성 스크립트
 * 실행: php create_page_files_table.php
 */

// Database 설정 파일 로드
require_once __DIR__ . '/application/config/_env.func.php';
require_once __DIR__ . '/application/config/_db_info.php';

try {
    // PDO 연결
    $pdo = getDBConnection();
    
    echo "✅ 데이터베이스 연결 성공!\n";
    echo "   Host: " . DB_HOST . "\n";
    echo "   Database: " . DB_NAME . "\n\n";
    
    // SQL 파일 읽기
    $sqlFile = __DIR__ . '/database/add_page_files_table.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL 파일을 찾을 수 없습니다: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    echo "📄 SQL 실행 중...\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo $sql . "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // SQL 실행
    $pdo->exec($sql);
    
    echo "✅ page_files 테이블 생성 완료!\n\n";
    
    // 테이블 구조 확인
    echo "📋 테이블 구조 확인:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    $stmt = $pdo->query("DESCRIBE page_files");
    $columns = $stmt->fetchAll();
    
    printf("%-20s %-20s %-10s %-10s\n", "Field", "Type", "Null", "Key");
    echo str_repeat("-", 70) . "\n";
    
    foreach ($columns as $column) {
        printf("%-20s %-20s %-10s %-10s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Key']
        );
    }
    
    echo "\n✅ 모든 작업이 완료되었습니다!\n";
    
} catch (Exception $e) {
    echo "❌ 오류 발생: " . $e->getMessage() . "\n";
    exit(1);
}
