<?php
// 메뉴 데이터 확인
require_once __DIR__ . '/../application/config/_db_info.php';
require_once __DIR__ . '/../application/config/_db_func.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>메뉴 데이터 확인</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .empty { color: red; font-weight: bold; }
        .filled { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📋 메뉴 데이터 확인</h1>
    
    <?php
    try {
        $menus = getDbArray("SELECT * FROM header_menu ORDER BY menu_order ASC, id ASC") ?? [];
        
        if (empty($menus)) {
            echo '<p style="color: red;">❌ 메뉴가 없습니다.</p>';
        } else {
            echo '<p style="color: green;">✅ 총 ' . count($menus) . '개의 메뉴</p>';
            echo '<table>';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>메뉴명</th>';
            echo '<th>타입</th>';
            echo '<th>menu_target</th>';
            echo '<th>custom_url</th>';
            echo '<th>use_redirect</th>';
            echo '<th>순서</th>';
            echo '<th>상태</th>';
            echo '</tr>';
            
            foreach ($menus as $menu) {
                echo '<tr>';
                echo '<td>' . $menu['id'] . '</td>';
                echo '<td>' . htmlspecialchars($menu['menu_name']) . '</td>';
                echo '<td>' . $menu['menu_type'] . '</td>';
                
                // menu_target
                if (empty($menu['menu_target'])) {
                    echo '<td class="empty">(빈값)</td>';
                } else {
                    echo '<td class="filled">' . htmlspecialchars($menu['menu_target']) . '</td>';
                }
                
                // custom_url
                if (empty($menu['custom_url'])) {
                    echo '<td class="empty">(빈값)</td>';
                } else {
                    echo '<td class="filled">' . htmlspecialchars($menu['custom_url']) . '</td>';
                }
                
                echo '<td>' . $menu['use_redirect'] . '</td>';
                echo '<td>' . $menu['menu_order'] . '</td>';
                
                // 상태
                $status = [];
                if ($menu['is_active'] === 'N') $status[] = '비활성';
                if ($menu['is_hidden'] === 'Y') $status[] = '숨김';
                if ($menu['is_blocked'] === 'Y') $status[] = '차단';
                echo '<td>' . (empty($status) ? '정상' : implode(', ', $status)) . '</td>';
                
                echo '</tr>';
            }
            
            echo '</table>';
        }
        
    } catch (Exception $e) {
        echo '<p style="color: red;">❌ 오류: ' . $e->getMessage() . '</p>';
    }
    ?>
    
    <hr style="margin: 30px 0;">
    <p><a href="/admin/menu/header">← 메뉴 관리로 돌아가기</a></p>
</body>
</html>
