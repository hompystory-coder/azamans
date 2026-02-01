<?php
// 간단한 테이블 체크 스크립트
require_once __DIR__ . '/../application/config/_env.func.php';
require_once __DIR__ . '/../application/config/_sys.func.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>메뉴 테이블 확인</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">메뉴 테이블 확인</h1>
        
        <?php
        $tables = [
            'header_menu' => '헤더 메뉴',
            'footer_menu' => '푸터 메뉴',
            'menu_pages' => '메뉴 페이지'
        ];
        
        foreach ($tables as $table => $name):
            try {
                $count = getDbCnt("SELECT COUNT(*) FROM $table");
                $columns = getDbArray("SHOW COLUMNS FROM $table");
                ?>
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">✅ <?php echo $name; ?> (<?php echo $table; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>컬럼 수:</strong> <?php echo count($columns); ?>개</p>
                        <p><strong>데이터:</strong> <?php echo $count; ?>개</p>
                        <details>
                            <summary>컬럼 상세</summary>
                            <table class="table table-sm mt-2">
                                <thead>
                                    <tr>
                                        <th>컬럼명</th>
                                        <th>타입</th>
                                        <th>Null</th>
                                        <th>Key</th>
                                        <th>Default</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($columns as $col): ?>
                                        <tr>
                                            <td><?php echo $col['Field']; ?></td>
                                            <td><?php echo $col['Type']; ?></td>
                                            <td><?php echo $col['Null']; ?></td>
                                            <td><?php echo $col['Key']; ?></td>
                                            <td><?php echo $col['Default'] ?? 'NULL'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </details>
                    </div>
                </div>
                <?php
            } catch (Exception $e) {
                ?>
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">❌ <?php echo $name; ?> (<?php echo $table; ?>)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-danger">테이블이 존재하지 않습니다.</p>
                        <p><small><?php echo $e->getMessage(); ?></small></p>
                    </div>
                </div>
                <?php
            }
        endforeach;
        ?>
        
        <div class="mt-4">
            <a href="/admin/menu/header" class="btn btn-primary">메뉴 관리로 이동</a>
            <a href="/" class="btn btn-secondary">홈으로</a>
        </div>
    </div>
</body>
</html>
