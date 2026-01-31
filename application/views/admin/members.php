<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title); ?> - 관리자</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-header">
            <h1><?php echo xssFilter($title); ?></h1>
            <div class="admin-time">
                <?php echo date('Y년 m월 d일 H:i'); ?>
            </div>
        </header>
        
        <!-- 검색/필터 -->
        <div class="filter-section">
            <form method="GET" action="/admin/members" class="search-form">
                <select name="status" onchange="this.form.submit()">
                    <option value="">전체 상태</option>
                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>활성</option>
                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>비활성</option>
                    <option value="banned" <?php echo (isset($_GET['status']) && $_GET['status'] == 'banned') ? 'selected' : ''; ?>>차단</option>
                </select>
                
                <select name="level" onchange="this.form.submit()">
                    <option value="">전체 등급</option>
                    <option value="1" <?php echo (isset($_GET['level']) && $_GET['level'] == '1') ? 'selected' : ''; ?>>일반(1)</option>
                    <option value="5" <?php echo (isset($_GET['level']) && $_GET['level'] == '5') ? 'selected' : ''; ?>>정회원(5)</option>
                    <option value="9" <?php echo (isset($_GET['level']) && $_GET['level'] == '9') ? 'selected' : ''; ?>>관리자(9)</option>
                    <option value="10" <?php echo (isset($_GET['level']) && $_GET['level'] == '10') ? 'selected' : ''; ?>>최고관리자(10)</option>
                </select>
                
                <input type="text" 
                       name="search" 
                       placeholder="아이디, 이름, 이메일 검색..." 
                       value="<?php echo isset($_GET['search']) ? xssFilter($_GET['search']) : ''; ?>">
                <button type="submit" class="btn-primary">검색</button>
                <a href="/admin/members" class="btn-secondary">초기화</a>
            </form>
        </div>
        
        <!-- 통계 -->
        <div class="stats-mini">
            <div class="stat-item">
                <span class="stat-label">전체 회원</span>
                <span class="stat-value"><?php echo number_format($total ?? 0); ?>명</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">현재 페이지</span>
                <span class="stat-value"><?php echo $current_page; ?> / <?php echo $total_pages; ?></span>
            </div>
        </div>
        
        <!-- 회원 목록 -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>UID</th>
                        <th>아이디</th>
                        <th>이름</th>
                        <th>이메일</th>
                        <th>등급</th>
                        <th>포인트</th>
                        <th>게시물</th>
                        <th>댓글</th>
                        <th>상태</th>
                        <th>최근 로그인</th>
                        <th>가입일</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 40px;">
                            회원이 없습니다.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo $member['uid']; ?></td>
                            <td>
                                <strong><?php echo xssFilter($member['user_id']); ?></strong>
                                <?php if ($member['level'] >= 9): ?>
                                    <span class="badge badge-admin">관리자</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo xssFilter($member['name']); ?></td>
                            <td><?php echo xssFilter($member['email']); ?></td>
                            <td>
                                <span class="badge badge-level-<?php echo $member['level']; ?>">
                                    Lv.<?php echo $member['level']; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($member['point'] ?? 0); ?>P</td>
                            <td><?php echo number_format($member['post_count'] ?? 0); ?></td>
                            <td><?php echo number_format($member['comment_count'] ?? 0); ?></td>
                            <td>
                                <?php
                                $statusClass = [
                                    'active' => 'success',
                                    'inactive' => 'warning',
                                    'banned' => 'danger'
                                ];
                                $statusLabel = [
                                    'active' => '활성',
                                    'inactive' => '비활성',
                                    'banned' => '차단'
                                ];
                                $status = $member['status'] ?? 'active';
                                ?>
                                <span class="badge badge-<?php echo $statusClass[$status]; ?>">
                                    <?php echo $statusLabel[$status]; ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                echo $member['last_login'] 
                                    ? date('Y-m-d H:i', strtotime($member['last_login'])) 
                                    : '-'; 
                                ?>
                            </td>
                            <td><?php echo date('Y-m-d', strtotime($member['reg_date'])); ?></td>
                            <td>
                                <a href="/admin/member/<?php echo $member['uid']; ?>" class="btn-sm btn-info">
                                    상세
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 페이징 -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['level']) ? '&level=' . $_GET['level'] : ''; ?>" class="page-link">처음</a>
                <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['level']) ? '&level=' . $_GET['level'] : ''; ?>" class="page-link">이전</a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 5);
            $end = min($total_pages, $page + 5);
            
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['level']) ? '&level=' . $_GET['level'] : ''; ?>" 
                   class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['level']) ? '&level=' . $_GET['level'] : ''; ?>" class="page-link">다음</a>
                <a href="?page=<?php echo $total_pages; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['level']) ? '&level=' . $_GET['level'] : ''; ?>" class="page-link">마지막</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
