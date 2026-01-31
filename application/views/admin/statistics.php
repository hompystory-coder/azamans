<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title); ?> - 관리자</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard-dynamic-subset.css" />
    <link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="d-flex">
        <?php include __DIR__ . '/_sidebar.php'; ?>
        
        <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0">
                    <i class="fas fa-chart-bar text-main me-2"></i><?php echo xssFilter($title); ?>
                </h1>
                <select id="periodSelect" class="form-select" style="width: auto;" onchange="changePeriod()">
                    <option value="7" <?php echo $period == 7 ? 'selected' : ''; ?>>최근 7일</option>
                    <option value="30" <?php echo $period == 30 ? 'selected' : ''; ?>>최근 30일</option>
                    <option value="90" <?php echo $period == 90 ? 'selected' : ''; ?>>최근 90일</option>
                </select>
            </div>
            
            <!-- 방문자 통계 카드 -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">오늘 방문자</h6>
                            <h2 class="mb-0 fw-bold text-primary"><?php echo number_format($visitorStats['today']); ?></h2>
                            <small class="text-muted">어제: <?php echo number_format($visitorStats['yesterday']); ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">이번 주 방문자</h6>
                            <h2 class="mb-0 fw-bold text-success"><?php echo number_format($visitorStats['week']); ?></h2>
                            <small class="text-muted">최근 7일</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">이번 달 방문자</h6>
                            <h2 class="mb-0 fw-bold text-warning"><?php echo number_format($visitorStats['month']); ?></h2>
                            <small class="text-muted">최근 30일</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">전체 방문자</h6>
                            <h2 class="mb-0 fw-bold text-danger"><?php echo number_format($visitorStats['total']); ?></h2>
                            <small class="text-muted">누적</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 게시물 통계 카드 -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">오늘 게시물</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($postStats['today']); ?></h2>
                            <small class="text-muted">어제: <?php echo number_format($postStats['yesterday']); ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">이번 주 게시물</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($postStats['week']); ?></h2>
                            <small class="text-muted">최근 7일</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">오늘 가입자</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($memberStats['today']); ?></h2>
                            <small class="text-muted">어제: <?php echo number_format($memberStats['yesterday']); ?></small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">활성 사용자</h6>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($stats['active_users']); ?></h2>
                            <small class="text-muted">최근 7일 로그인</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 차트 -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">일별 방문자</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="visitChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">게시물 작성 추이</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="postChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 인기 게시물 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">인기 게시물 TOP 10</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">순위</th>
                                    <th width="100">게시판</th>
                                    <th>제목</th>
                                    <th width="100">작성자</th>
                                    <th width="80">조회수</th>
                                    <th width="80">댓글</th>
                                    <th width="120">작성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($topPosts)): ?>
                                    <?php $rank = 1; foreach ($topPosts as $post): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?php echo $rank++; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo xssFilter($post['board_id']); ?></span></td>
                                        <td>
                                            <a href="/bbs/<?php echo $post['board_id']; ?>/view/<?php echo $post['uid']; ?>" 
                                               class="text-decoration-none" target="_blank">
                                                <?php echo xssFilter($post['subject']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo xssFilter($post['writer']); ?></td>
                                        <td class="text-center"><?php echo number_format($post['views']); ?></td>
                                        <td class="text-center"><?php echo number_format($post['comments'] ?? 0); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">데이터가 없습니다.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    // 일별 방문자 차트
    const visitData = <?php echo json_encode(array_column($dailyVisits, 'count')); ?>;
    const visitLabels = <?php echo json_encode(array_column($dailyVisits, 'date')); ?>;
    
    new Chart(document.getElementById('visitChart'), {
        type: 'line',
        data: {
            labels: visitLabels,
            datasets: [{
                label: '일별 방문자',
                data: visitData,
                borderColor: 'rgb(255, 165, 15)',
                backgroundColor: 'rgba(255, 165, 15, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // 게시물 차트
    const postData = <?php echo json_encode(array_column($dailyPosts, 'count')); ?>;
    const postLabels = <?php echo json_encode(array_column($dailyPosts, 'date')); ?>;
    
    new Chart(document.getElementById('postChart'), {
        type: 'bar',
        data: {
            labels: postLabels,
            datasets: [{
                label: '게시물 수',
                data: postData,
                backgroundColor: 'rgba(52, 152, 219, 0.5)',
                borderColor: 'rgb(52, 152, 219)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    function changePeriod() {
        const period = document.getElementById('periodSelect').value;
        window.location.href = '?period=' + period;
    }
    </script>
</body>
</html>
