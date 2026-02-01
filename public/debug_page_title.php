<?php
// 페이지 타이틀 디버깅
require_once __DIR__ . '/../application/config/_db_info.php';
require_once __DIR__ . '/../application/config/_db_func.php';
require_once __DIR__ . '/../application/config/_security.func.php';

$menuId = $_GET['id'] ?? 2;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>페이지 타이틀 디버깅</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .info { background: #e3f2fd; padding: 15px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .code { background: #f5f5f5; padding: 10px; font-family: monospace; border: 1px solid #ddd; }
        h2 { color: #333; border-bottom: 2px solid #2196F3; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h1>🔍 페이지 타이틀 디버깅</h1>
    
    <div class="info">
        <strong>테스트 URL:</strong> 
        <a href="/page/<?php echo $menuId; ?>">/page/<?php echo $menuId; ?></a>
    </div>
    
    <?php
    try {
        // 메뉴 정보 조회
        $menu = getUidData("SELECT * FROM header_menu WHERE id = ?", [$menuId]);
        
        if (!$menu) {
            echo '<p style="color: red;">❌ 메뉴 ID ' . $menuId . '를 찾을 수 없습니다.</p>';
        } else {
            echo '<h2>✅ 메뉴 정보 (ID: ' . $menuId . ')</h2>';
            
            echo '<div class="code">';
            echo '<strong>menu_name:</strong> "' . htmlspecialchars($menu['menu_name']) . '"<br>';
            echo '<strong>menu_type:</strong> ' . $menu['menu_type'] . '<br>';
            echo '<strong>menu_target:</strong> "' . htmlspecialchars($menu['menu_target'] ?? '') . '"<br>';
            echo '<strong>custom_url:</strong> "' . htmlspecialchars($menu['custom_url'] ?? '') . '"<br>';
            echo '<strong>use_redirect:</strong> ' . $menu['use_redirect'] . '<br>';
            echo '<strong>parent_id:</strong> ' . $menu['parent_id'] . '<br>';
            echo '<strong>is_active:</strong> ' . $menu['is_active'] . '<br>';
            echo '<strong>is_hidden:</strong> ' . $menu['is_hidden'] . '<br>';
            echo '<strong>is_blocked:</strong> ' . $menu['is_blocked'] . '<br>';
            echo '</div>';
            
            // 페이지 컨트롤러에서 실행되는 코드 시뮬레이션
            echo '<h2>📝 컨트롤러에서 전달하는 데이터</h2>';
            
            $title = xssFilter($menu['menu_name']);
            
            echo '<div class="code">';
            echo '<strong>$data["title"]:</strong> "' . htmlspecialchars($title) . '"<br>';
            echo '</div>';
            
            // _header.php에서 사용되는 코드
            echo '<h2>🌐 브라우저 타이틀</h2>';
            echo '<div class="code">';
            echo '<strong>&lt;title&gt;</strong> ' . htmlspecialchars($title ?? 'MVC Framework') . ' <strong>&lt;/title&gt;</strong>';
            echo '</div>';
            
            // 페이지 콘텐츠 확인
            $page = getUidData("SELECT content FROM menu_pages WHERE menu_id = ?", [$menuId]);
            
            echo '<h2>📄 페이지 콘텐츠</h2>';
            if ($page && !empty($page['content'])) {
                echo '<div class="code">';
                echo htmlspecialchars(substr($page['content'], 0, 200));
                if (strlen($page['content']) > 200) echo '...';
                echo '</div>';
            } else {
                echo '<p style="color: orange;">⚠️ 페이지 콘텐츠가 없습니다.</p>';
            }
        }
        
    } catch (Exception $e) {
        echo '<p style="color: red;">❌ 오류: ' . $e->getMessage() . '</p>';
    }
    ?>
    
    <hr style="margin: 30px 0;">
    
    <p>다른 메뉴 ID 테스트:</p>
    <ul>
        <li><a href="?id=1">ID 1 확인</a></li>
        <li><a href="?id=2">ID 2 확인</a></li>
        <li><a href="?id=3">ID 3 확인</a></li>
        <li><a href="?id=4">ID 4 확인</a></li>
    </ul>
    
    <p><a href="/check_menu_data.php">← 전체 메뉴 데이터 확인</a></p>
</body>
</html>
