<?php
require_once __DIR__ . '/application/config/config.php';
require_once __DIR__ . '/application/config/_db.php';
require_once __DIR__ . '/application/config/_db_func.php';
require_once __DIR__ . '/application/config/_security.func.php';
require_once __DIR__ . '/application/config/_sys.func.php';

// 테스트: header_logo 삭제
$logoType = 'header_logo';

echo "===== 로고 삭제 테스트 =====\n";

// 1. 현재 값 확인
$logoData = getUidData("SELECT config_value FROM site_config WHERE config_key = ?", [$logoType]);
$logoUrl = $logoData['config_value'] ?? '';

echo "현재 로고 URL: $logoUrl\n";

// 2. 파일 경로 확인
if ($logoUrl) {
    $filepath = BASE_PATH . '/' . ltrim($logoUrl, '/');
    echo "파일 경로: $filepath\n";
    echo "파일 존재: " . (file_exists($filepath) ? "예" : "아니오") . "\n";
    
    // 3. 파일 삭제 테스트
    if (file_exists($filepath)) {
        $deleted = @unlink($filepath);
        echo "파일 삭제: " . ($deleted ? "성공" : "실패") . "\n";
    }
}

// 4. DB 업데이트 테스트
$keys = [$logoType, $logoType . '_width', $logoType . '_height'];
foreach ($keys as $key) {
    $sql = "UPDATE site_config SET config_value = '', updated_at = NOW() WHERE config_key = ?";
    $stmt = getDBConnection()->prepare($sql);
    if ($stmt->execute([$key])) {
        echo "DB 업데이트 [$key]: " . $stmt->rowCount() . " 행 업데이트\n";
    }
}

echo "\n===== 테스트 완료 =====\n";

// 5. 최종 확인
$finalData = getUidData("SELECT config_value FROM site_config WHERE config_key = ?", [$logoType]);
echo "최종 로고 값: " . ($finalData['config_value'] ?? '(비어있음)') . "\n";
