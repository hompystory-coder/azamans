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
                <p class="big-number"><?php echo number_format($stats['total_visits']); ?></p>
                <span class="trend up">▲ 12.5%</span>
            </div>
            <div class="summary-card">
                <h3>신규 회원</h3>
                <p class="big-number"><?php echo number_format($stats['new_members']); ?></p>
                <span class="trend up">▲ 8.3%</span>
            </div>
            <div class="summary-card">
                <h3>새 게시물</h3>
                <p class="big-number"><?php echo number_format($stats['new_posts']); ?></p>
                <span class="trend down">▼ 2.1%</span>
            </div>
            <div class="summary-card">
                <h3>활성 사용자</h3>
                <p class="big-number"><?php echo number_format($stats['active_users']); ?></p>
                <span class="trend up">▲ 15.7%</span>
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
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td>샘플 게시물 제목 <?php echo $i; ?></td>
                            <td>자유게시판</td>
                            <td><?php echo number_format(rand(100, 1000)); ?></td>
                            <td><?php echo rand(10, 100); ?></td>
                            <td><?php echo rand(5, 50); ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    
    <script>
    // 차트 데이터 (실제로는 PHP에서 동적으로 생성)
    const visitData = {
        labels: ['1일', '2일', '3일', '4일', '5일', '6일', '7일'],
        datasets: [{
            label: '방문자',
            data: [120, 190, 150, 170, 180, 200, 230],
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            tension: 0.4
        }]
    };
    
    const memberData = {
        labels: ['1주', '2주', '3주', '4주'],
        datasets: [{
            label: '신규 회원',
            data: [12, 19, 15, 25],
            backgroundColor: '#2ecc71'
        }]
    };
    
    const postData = {
        labels: ['월', '화', '수', '목', '금', '토', '일'],
        datasets: [{
            label: '게시물',
            data: [25, 30, 28, 35, 32, 20, 15],
            backgroundColor: '#e74c3c'
        }]
    };
    
    const boardData = {
        labels: ['공지사항', '자유게시판', 'Q&A', '갤러리'],
        datasets: [{
            data: [45, 120, 78, 95],
            backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#9b59b6']
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
        // 여기서 AJAX로 데이터를 새로 로드하고 차트를 업데이트
        console.log('Period changed to:', period);
    }
    </script>
</body>
</html>
