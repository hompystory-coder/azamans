<?php
// ID 2번 메뉴의 타이틀 직접 테스트
require_once __DIR__ . '/../application/config/_db_info.php';
require_once __DIR__ . '/../application/config/_db_func.php';
require_once __DIR__ . '/../application/config/_security.func.php';

$menuId = 2;

// 메뉴 정보 조회 (Page 컨트롤러와 동일한 쿼리)
$menu = getUidData("SELECT * FROM header_menu WHERE id = ? AND menu_type = 'page'", [$menuId]);

if (!$menu) {
    die("메뉴를 찾을 수 없습니다.");
}

// 페이지 콘텐츠 조회
$page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$menuId]);
$content = $page['content'] ?? '<p>페이지 내용이 없습니다.</p>';

// Page 컨트롤러와 동일하게 데이터 구성
$data = [
    'title' => xssFilter($menu['menu_name']),
    'menu' => $menu,
    'content' => $content
];

// extract 실행 (Controller의 view 메서드와 동일)
extract($data);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title><?php echo xssFilter($title ?? 'MVC Framework'); ?></title>
</head>
<body>
    <h1>페이지 타이틀 테스트</h1>
    
    <h2>원본 데이터:</h2>
    <pre><?php print_r($menu); ?></pre>
    
    <h2>$data 배열:</h2>
    <pre><?php print_r($data); ?></pre>
    
    <h2>extract 후 $title 변수:</h2>
    <pre><?php echo $title; ?></pre>
    
    <h2>브라우저 타이틀에 표시될 값:</h2>
    <pre><?php echo xssFilter($title ?? 'MVC Framework'); ?></pre>
    
    <h2>HTML title 태그 소스:</h2>
    <textarea rows="3" cols="80" readonly><?php echo htmlspecialchars('<title>' . xssFilter($title ?? 'MVC Framework') . '</title>'); ?></textarea>
</body>
</html>
