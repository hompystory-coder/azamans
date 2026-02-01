<?php
require_once __DIR__ . '/../application/config/_env.func.php';
require_once __DIR__ . '/../application/config/_sys.func.php';

loadEnv(__DIR__ . '/../.env');

header('Content-Type: text/html; charset=utf-8');

echo "<h2>게시판 테이블 확인</h2>";

$db = getDbConnect();

// 테이블 목록
echo "<h3>BBS 관련 테이블</h3>";
$result = $db->query("SHOW TABLES LIKE 'bbs%'");
echo "<ul>";
while ($row = $result->fetch_array()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// bbs_list 테이블 확인
$result = $db->query("SHOW TABLES LIKE 'bbs_list'");
if ($result->num_rows > 0) {
    echo "<h3>bbs_list 테이블 구조</h3>";
    $result = $db->query("DESCRIBE bbs_list");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>필드</th><th>타입</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 게시판 목록
    echo "<h3>생성된 게시판 목록</h3>";
    $result = $db->query("SELECT * FROM bbs_list ORDER BY board_name ASC");
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>board_id</th><th>board_name</th><th>board_type</th><th>status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['board_id'] . "</td>";
            echo "<td>" . $row['board_name'] . "</td>";
            echo "<td>" . ($row['board_type'] ?? 'N/A') . "</td>";
            echo "<td>" . ($row['status'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>생성된 게시판이 없습니다.</p>";
    }
} else {
    echo "<p><strong>bbs_list 테이블이 존재하지 않습니다.</strong></p>";
}

// bbs_boards 테이블 확인
$result = $db->query("SHOW TABLES LIKE 'bbs_boards'");
if ($result->num_rows > 0) {
    echo "<h3>bbs_boards 테이블도 존재합니다</h3>";
    $result = $db->query("SELECT * FROM bbs_boards LIMIT 10");
    echo "<table border='1' cellpadding='5'>";
    $first = true;
    while ($row = $result->fetch_assoc()) {
        if ($first) {
            echo "<tr>";
            foreach (array_keys($row) as $key) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            $first = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
}
?>
