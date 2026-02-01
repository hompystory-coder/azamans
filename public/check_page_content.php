<?php
// 페이지 콘텐츠 확인
require_once __DIR__ . '/../application/config/_db_info.php';
require_once __DIR__ . '/../application/config/_db_func.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>페이지 콘텐츠 확인</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: top; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .content-preview { 
            max-height: 200px; 
            overflow-y: auto; 
            background: #f5f5f5; 
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .has-title { background-color: #ffebee !important; }
        .warning { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📄 menu_pages 테이블 확인</h1>
    
    <?php
    try {
        // 모든 페이지 콘텐츠 조회
        $pages = getDbArray("
            SELECT mp.*, hm.menu_name, hm.id as menu_id
            FROM menu_pages mp
            LEFT JOIN header_menu hm ON mp.menu_id = hm.id
            ORDER BY mp.menu_id ASC
        ") ?? [];
        
        if (empty($pages)) {
            echo '<p style="color: red;">❌ 페이지 콘텐츠가 없습니다.</p>';
        } else {
            echo '<p style="color: green;">✅ 총 ' . count($pages) . '개의 페이지</p>';
            echo '<table>';
            echo '<tr>';
            echo '<th>menu_id</th>';
            echo '<th>메뉴명</th>';
            echo '<th>content에 &lt;title&gt; 포함?</th>';
            echo '<th>콘텐츠 미리보기</th>';
            echo '</tr>';
            
            foreach ($pages as $page) {
                $content = $page['content'] ?? '';
                $hasTitle = (stripos($content, '<title>') !== false);
                
                $rowClass = $hasTitle ? 'has-title' : '';
                
                echo '<tr class="' . $rowClass . '">';
                echo '<td>' . $page['menu_id'] . '</td>';
                echo '<td>' . htmlspecialchars($page['menu_name']) . '</td>';
                
                if ($hasTitle) {
                    // <title> 태그 추출
                    preg_match('/<title>(.*?)<\/title>/is', $content, $matches);
                    $titleContent = $matches[1] ?? '';
                    echo '<td class="warning">⚠️ YES<br><small>내용: "' . htmlspecialchars($titleContent) . '"</small></td>';
                } else {
                    echo '<td>✅ NO</td>';
                }
                
                // 콘텐츠 미리보기 (처음 500자)
                $preview = htmlspecialchars(substr($content, 0, 500));
                if (strlen($content) > 500) $preview .= "\n\n... (총 " . strlen($content) . "자)";
                
                echo '<td><div class="content-preview">' . $preview . '</div></td>';
                echo '</tr>';
            }
            
            echo '</table>';
            
            // 경고 메시지
            if (array_filter($pages, function($p) { return stripos($p['content'] ?? '', '<title>') !== false; })) {
                echo '<div style="background: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin-top: 20px;">';
                echo '<h3 style="color: #d32f2f; margin-top: 0;">⚠️ 경고!</h3>';
                echo '<p>일부 페이지 콘텐츠에 &lt;title&gt; 태그가 포함되어 있습니다.</p>';
                echo '<p>이로 인해 _header.php의 타이틀이 무시되고 content의 타이틀이 표시될 수 있습니다.</p>';
                echo '<p><strong>해결 방법:</strong> 해당 페이지를 편집해서 &lt;title&gt; 태그를 제거하세요.</p>';
                echo '</div>';
            }
        }
        
    } catch (Exception $e) {
        echo '<p style="color: red;">❌ 오류: ' . $e->getMessage() . '</p>';
    }
    ?>
    
    <hr style="margin: 30px 0;">
    <p><a href="/admin/menu/header">← 메뉴 관리로 돌아가기</a></p>
</body>
</html>
