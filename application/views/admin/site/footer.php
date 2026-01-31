<?php
// 헤더 코드와 거의 동일하므로 간단히 복사 후 수정
$content = file_get_contents(__DIR__ . '/header.php');
$content = str_replace('헤더 코드', '푸터 코드', $content);
$content = str_replace('header_code', 'footer_code', $content);
$content = str_replace('<head>', '<body>', $content);
$content = str_replace('</head>', '</body>', $content);
echo $content;
?>
