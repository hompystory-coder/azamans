<?php
require_once 'application/config/_env.func.php';
require_once 'application/config/_sys.func.php';

echo "=== 메뉴 테이블 확인 ===\n\n";

$tables = ['header_menu', 'footer_menu', 'menu_pages'];

foreach ($tables as $table) {
    $result = getDbArray("SHOW TABLES LIKE '$table'");
    if (!empty($result)) {
        echo "✅ $table 테이블 존재\n";
        
        // 컬럼 정보
        $columns = getDbArray("SHOW COLUMNS FROM $table");
        echo "   컬럼 수: " . count($columns) . "개\n";
        
        // 데이터 개수
        $count = getDbCnt("SELECT COUNT(*) FROM $table");
        echo "   데이터: {$count}개\n\n";
    } else {
        echo "❌ $table 테이블 없음\n\n";
    }
}
