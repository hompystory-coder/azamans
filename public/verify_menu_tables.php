<?php
require_once __DIR__ . '/application/config/_env.func.php';
require_once __DIR__ . '/application/config/_sys.func.php';

loadEnv(__DIR__ . '/.env');

$db = getDbConnect();

echo "<h2>메뉴 테이블 생성 확인</h2>";

// header_menu 테이블 확인
$result = $db->query("SHOW TABLES LIKE 'header_menu'");
if ($result->num_rows > 0) {
    echo "✅ header_menu 테이블 생성 완료<br>";
    $columns = $db->query("DESCRIBE header_menu");
    echo "<pre>";
    while ($col = $columns->fetch_assoc()) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
} else {
    echo "❌ header_menu 테이블 없음<br>";
}

// footer_menu 테이블 확인
$result = $db->query("SHOW TABLES LIKE 'footer_menu'");
if ($result->num_rows > 0) {
    echo "✅ footer_menu 테이블 생성 완료<br>";
    $columns = $db->query("DESCRIBE footer_menu");
    echo "<pre>";
    while ($col = $columns->fetch_assoc()) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
} else {
    echo "❌ footer_menu 테이블 없음<br>";
}

// menu_pages 테이블 확인
$result = $db->query("SHOW TABLES LIKE 'menu_pages'");
if ($result->num_rows > 0) {
    echo "✅ menu_pages 테이블 생성 완료<br>";
    $columns = $db->query("DESCRIBE menu_pages");
    echo "<pre>";
    while ($col = $columns->fetch_assoc()) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
    echo "</pre>";
} else {
    echo "❌ menu_pages 테이블 없음<br>";
}

echo "<br><h3>🎉 모든 테이블이 정상적으로 생성되었습니다!</h3>";
echo "<p><a href='/admin/menu/header'>메뉴 관리 페이지로 이동 →</a></p>";
