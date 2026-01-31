<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>통계 - 관리자</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">
    
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1>통계 대시보드</h1>
            <div class="date-range-selector">
                <select id="periodSelect" onchange="changePeriod()">
                    <option value="7">최근 7일</option>
                    <option value="30" selected>최근 30일</option>
                    <option value="90">최근 90일</option>
                </select>
            </div>
        </div>
        
        <!-- 요약 통계 -->
        <div class="stats-summary">
            <div class="summary-card">
                <h3>총 방문자</h3>
                <p class="big-number"><?php echo number_format($stats['total_visitors'] ?? 0); ?></p>
                <span class="trend">기간 내 고유 방문자</span>
            </div>
            <div class="summary-card">
                <h3>신규 회원</h3>
                <p class="big-number"><?php echo number_format($stats['new_members'] ?? 0); ?></p>
                <span class="trend">기간 내 가입</span>
            </div>
            <div class="summary-card">
                <h3>새 게시물</h3>
                <p class="big-number"><?php echo number_format($stats['new_posts'] ?? 0); ?></p>
                <span class="trend">기간 내 작성</span>
            </div>
            <div class="summary-card">
                <h3>활성 사용자</h3>
                <p class="big-number"><?php echo number_format($stats['active_users'] ?? 0); ?></p>
                <span class="trend">최근 7일 로그인</span>
            </div>
        </div>
        
        <!-- 차트 그리드 -->
        <div class="charts-grid">
            <div class="chart-panel">
                <h3>일별 방문자</h3>
                <canvas id="visitChart"></canvas>
            </div>
            
            <div class="chart-panel">
                <h3>회원 가입 추이</h3>
                <canvas id="memberChart"></canvas>
            </div>
            
            <div class="chart-panel">
                <h3>게시물 작성 추이</h3>
                <canvas id="postChart"></canvas>
            </div>
            
            <div class="chart-panel">
                <h3>게시판별 게시물 수</h3>
                <canvas id="boardChart"></canvas>
            </div>
        </div>
        
        <!-- 상세 통계 테이블 -->
        <div class="admin-panel">
            <div class="panel-header">
                <h2>인기 게시물 TOP 10</h2>
            </div>
            <div class="panel-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>순위</th>
                            <th>제목</th>
                            <th>게시판</th>
                            <th>조회수</th>
                            <th>좋아요</th>
                            <th>댓글</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($topPosts)): ?>
                            <?php foreach ($topPosts as $idx => $post): ?>
                            <tr>
                                <td><?php echo $idx + 1; ?></td>
                                <td>
                                    <a href="/bbs/<?php echo xssFilter($post['board_id']); ?>/view/<?php echo $post['uid']; ?>">
                                        <?php echo xssFilter($post['subject']); ?>
                                    </a>
                                </td>
                                <td><?php echo xssFilter($post['board_id']); ?></td>
                                <td><?php echo number_format($post['views']); ?></td>
                                <td><?php echo $post['comments']; ?></td>
                                <td><?php echo xssFilter($post['writer']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center">데이터가 없습니다</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <script>
    // PHP 데이터를 JavaScript로 전달
    const dailyVisits = <?php echo json_encode($dailyVisits ?? []); ?>;
    const dailySignups = <?php echo json_encode($dailySignups ?? []); ?>;
    const dailyPosts = <?php echo json_encode($dailyPosts ?? []); ?>;
    const postsByBoard = <?php echo json_encode($postsByBoard ?? []); ?>;
    
    // 차트 데이터 생성
    const visitData = {
        labels: dailyVisits.map(d => d.date),
        datasets: [{
            label: '방문자',
            data: dailyVisits.map(d => d.count),
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            tension: 0.4
        }]
    };
    
    const memberData = {
        labels: dailySignups.map(d => d.date),
        datasets: [{
            label: '신규 회원',
            data: dailySignups.map(d => d.count),
            backgroundColor: '#2ecc71'
        }]
    };
    
    const postData = {
        labels: dailyPosts.map(d => d.date),
        datasets: [{
            label: '게시물',
            data: dailyPosts.map(d => d.count),
            backgroundColor: '#e74c3c'
        }]
    };
    
    const boardData = {
        labels: postsByBoard.map(b => b.board_name),
        datasets: [{
            data: postsByBoard.map(b => b.count),
            backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#9b59b6', '#e74c3c', '#1abc9c', '#34495e', '#95a5a6']
        }]
    };
    
    // 차트 생성
    new Chart(document.getElementById('visitChart'), {
        type: 'line',
        data: visitData,
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    new Chart(document.getElementById('memberChart'), {
        type: 'bar',
        data: memberData,
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    new Chart(document.getElementById('postChart'), {
        type: 'bar',
        data: postData,
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    new Chart(document.getElementById('boardChart'), {
        type: 'doughnut',
        data: boardData,
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    function changePeriod() {
        const period = document.getElementById('periodSelect').value;
        window.location.href = '/admin/statistics?period=' + period;
    }
    </script>
</body>
</html>
