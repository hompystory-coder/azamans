<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?= xssFilter($title) ?></h2>
                <p class="text-muted mb-0"><?= date('Y년 m월 d일 H:i') ?></p>
            </div>
        </div>
        
        <!-- 필터 영역 -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="/admin/poststats" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">통계 유형</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="daily" <?= ($type ?? 'daily') == 'daily' ? 'selected' : '' ?>>일별</option>
                            <option value="monthly" <?= ($type ?? '') == 'monthly' ? 'selected' : '' ?>>월별</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">시작일</label>
                        <input type="date" name="start_date" class="form-control" value="<?= xssFilter($start_date ?? date('Y-m-01')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">종료일</label>
                        <input type="date" name="end_date" class="form-control" value="<?= xssFilter($end_date ?? date('Y-m-d')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">게시판</label>
                        <select name="board_id" class="form-select">
                            <option value="">전체 게시판</option>
                            <?php if (!empty($boards)): ?>
                                <?php foreach ($boards as $board): ?>
                                <option value="<?= xssFilter($board['board_id']) ?>" <?= ($board_filter ?? '') == $board['board_id'] ? 'selected' : '' ?>>
                                    <?= xssFilter($board['board_name']) ?>
                                </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> 검색
                        </button>
                        <a href="/admin/poststats" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> 초기화
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 통계 카드 -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($total_posts ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">총 게시물 수</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($today_posts ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">오늘 게시물</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($week_posts ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">이번 주 게시물</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-info mb-2">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($avg_daily ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">일평균 게시물</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 차트 영역 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar"></i> 게시물 트렌드
                </h5>
            </div>
            <div class="card-body">
                <canvas id="postsTrendChart" style="height: 300px;"></canvas>
            </div>
        </div>
        
        <!-- 게시판별 통계 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list-ul"></i> 게시판별 통계
                </h5>
                <span class="badge bg-primary">총 <?= count($board_stats ?? []) ?>개 게시판</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%">#</th>
                                <th style="width: 20%">게시판</th>
                                <th style="width: 15%">게시물 수</th>
                                <th style="width: 15%">댓글 수</th>
                                <th style="width: 15%">조회수</th>
                                <th style="width: 15%">평균 조회수</th>
                                <th style="width: 15%">최근 게시물</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($board_stats)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    통계 데이터가 없습니다.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $rank = 1; ?>
                                <?php foreach ($board_stats as $stat): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-trophy"></i> <?= $rank ?>
                                            </span>
                                        <?php else: ?>
                                            <?= $rank ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/admin/boards/<?= xssFilter($stat['board_id']) ?>" class="text-decoration-none">
                                            <strong><?= xssFilter($stat['board_name']) ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= number_format($stat['post_count'] ?? 0) ?>개
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= number_format($stat['comment_count'] ?? 0) ?>개
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= number_format($stat['total_views'] ?? 0) ?>회
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= number_format($stat['avg_views'] ?? 0, 1) ?>회
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        <?= $stat['last_post_date'] ? date('Y-m-d H:i', strtotime($stat['last_post_date'])) : '-' ?>
                                    </td>
                                </tr>
                                <?php $rank++; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- 작성자별 통계 -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users"></i> 작성자별 통계 (TOP 10)
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%">순위</th>
                                <th style="width: 20%">작성자</th>
                                <th style="width: 15%">게시물 수</th>
                                <th style="width: 15%">댓글 수</th>
                                <th style="width: 15%">총 조회수</th>
                                <th style="width: 25%">최근 활동</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($author_stats)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    통계 데이터가 없습니다.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php $rank = 1; ?>
                                <?php foreach ($author_stats as $author): ?>
                                <tr>
                                    <td class="text-center">
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-warning fs-6">
                                                <i class="fas fa-crown"></i> <?= $rank ?>
                                            </span>
                                        <?php else: ?>
                                            <?= $rank ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/admin/member/<?= xssFilter($author['uid']) ?>" class="text-decoration-none">
                                            <strong><?= xssFilter($author['user_id']) ?></strong>
                                        </a>
                                        <br>
                                        <small class="text-muted"><?= xssFilter($author['name']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?= number_format($author['post_count'] ?? 0) ?>개
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= number_format($author['comment_count'] ?? 0) ?>개
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= number_format($author['total_views'] ?? 0) ?>회
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        <?= $author['last_activity'] ? date('Y-m-d H:i', strtotime($author['last_activity'])) : '-' ?>
                                    </td>
                                </tr>
                                <?php $rank++; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// 게시물 트렌드 차트
const ctx = document.getElementById('postsTrendChart');
if (ctx) {
    <?php
    $labels = [];
    $data = [];
    if (!empty($stats)) {
        foreach ($stats as $stat) {
            if ($type === 'monthly') {
                $labels[] = $stat['month'] ?? '';
            } else {
                $labels[] = $stat['date'] ?? '';
            }
            $data[] = $stat['count'] ?? 0;
        }
    }
    ?>
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: '게시물 수',
                data: <?= json_encode($data) ?>,
                backgroundColor: 'rgba(255, 165, 15, 0.2)',
                borderColor: 'rgba(255, 165, 15, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
