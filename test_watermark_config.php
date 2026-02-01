<?php
// DB 연결
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/application/core/functions.php';
require_once BASE_PATH . '/application/config/config.php';

// site_config에서 워터마크 설정 읽기
$configs = getDbArray("SELECT config_key, config_value FROM site_config WHERE config_key LIKE 'watermark%' OR config_key LIKE 'thumb%' OR config_key LIKE 'image_%' ORDER BY config_key");

echo "<h2>워터마크 & 이미지 설정</h2>\n";
echo "<table border='1' cellpadding='5'>\n";
echo "<tr><th>설정 키</th><th>값</th></tr>\n";
foreach ($configs as $config) {
    echo "<tr><td>{$config['config_key']}</td><td>{$config['config_value']}</td></tr>\n";
}
echo "</table>\n";

// 워터마크 파일 존재 여부
$watermarkImage = '';
foreach ($configs as $config) {
    if ($config['config_key'] === 'watermark_image') {
        $watermarkImage = $config['config_value'];
        break;
    }
}

echo "<h3>워터마크 이미지 경로</h3>\n";
echo "<p>DB 저장 경로: {$watermarkImage}</p>\n";

if ($watermarkImage) {
    $publicPath = BASE_PATH . '/public';
    $fullPath = $publicPath . $watermarkImage;
    echo "<p>전체 경로: {$fullPath}</p>\n";
    echo "<p>파일 존재: " . (file_exists($fullPath) ? '✅ YES' : '❌ NO') . "</p>\n";
    
    if (file_exists($fullPath)) {
        echo "<p>파일 크기: " . filesize($fullPath) . " bytes</p>\n";
        echo "<p>이미지 미리보기:</p>\n";
        echo "<img src='/public{$watermarkImage}' style='max-width: 200px; border: 1px solid #ccc; padding: 10px; background: #f0f0f0;'>\n";
    }
}
?>
