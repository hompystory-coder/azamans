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
                <form method="GET" action="/admin/tracking" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">기간 선택</label>
                        <select name="period" class="form-select">
                            <option value="today" <?= ($period ?? 'today') == 'today' ? 'selected' : '' ?>>오늘</option>
                            <option value="yesterday" <?= ($period ?? '') == 'yesterday' ? 'selected' : '' ?>>어제</option>
                            <option value="week" <?= ($period ?? '') == 'week' ? 'selected' : '' ?>>최근 7일</option>
                            <option value="month" <?= ($period ?? '') == 'month' ? 'selected' : '' ?>>최근 30일</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IP 검색</label>
                        <input type="text" name="ip" class="form-control" placeholder="IP 주소 입력" value="<?= xssFilter($search_ip ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Referer 검색</label>
                        <input type="text" name="referer" class="form-control" placeholder="참조 URL 입력" value="<?= xssFilter($search_referer ?? '') ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> 검색
                        </button>
                        <a href="/admin/tracking" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i>
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
                        <div class="text-muted mb-2">
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($total_visits ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">총 방문 수</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-primary mb-2">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($unique_visitors ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">순 방문자</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-warning mb-2">
                            <i class="fas fa-ban fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($blocked_ips ?? 0) ?></h3>
                        <p class="text-muted mb-0 small">차단된 IP</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="text-success mb-2">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h3 class="mb-1"><?= number_format($avg_time ?? 0) ?>초</h3>
                        <p class="text-muted mb-0 small">평균 체류시간</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 방문자 추적 목록 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list-ul"></i> 방문자 추적 목록
                </h5>
                <span class="badge bg-primary">총 <?= number_format($total ?? 0) ?>개</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 15%">IP 주소</th>
                                <th style="width: 15%">방문 시간</th>
                                <th style="width: 30%">페이지</th>
                                <th style="width: 25%">Referer</th>
                                <th style="width: 10%">User Agent</th>
                                <th style="width: 5%">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($visits)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    방문 기록이 없습니다.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($visits as $visit): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary"><?= xssFilter($visit['ip']) ?></span>
                                        <?php if ($visit['is_blocked'] ?? false): ?>
                                        <span class="badge bg-danger ms-1">차단됨</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?= date('Y-m-d H:i:s', strtotime($visit['visit_time'])) ?></td>
                                    <td>
                                        <a href="<?= xssFilter($visit['page_url']) ?>" target="_blank" class="text-decoration-none">
                                            <?= xssFilter(substr($visit['page_url'], 0, 50)) . (strlen($visit['page_url']) > 50 ? '...' : '') ?>
                                        </a>
                                    </td>
                                    <td class="small">
                                        <?php if ($visit['referer']): ?>
                                        <a href="<?= xssFilter($visit['referer']) ?>" target="_blank" class="text-decoration-none">
                                            <?= xssFilter(substr($visit['referer'], 0, 40)) . (strlen($visit['referer']) > 40 ? '...' : '') ?>
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted">직접 방문</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <?php
                                        $ua = $visit['user_agent'];
                                        if (strpos($ua, 'Mobile') !== false) {
                                            echo '<i class="fas fa-mobile-alt text-primary"></i> Mobile';
                                        } elseif (strpos($ua, 'Chrome') !== false) {
                                            echo '<i class="fab fa-chrome text-success"></i> Chrome';
                                        } elseif (strpos($ua, 'Safari') !== false) {
                                            echo '<i class="fab fa-safari text-info"></i> Safari';
                                        } elseif (strpos($ua, 'Firefox') !== false) {
                                            echo '<i class="fab fa-firefox text-warning"></i> Firefox';
                                        } else {
                                            echo '<i class="fas fa-globe"></i> 기타';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (!($visit['is_blocked'] ?? false)): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="blockIP('<?= xssFilter($visit['ip']) ?>')">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-sm btn-outline-success" onclick="unblockIP('<?= xssFilter($visit['ip']) ?>')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if (!empty($visits)): ?>
            <div class="card-footer">
                <?php if (isset($pagination)): ?>
                    <?= $pagination ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- IP 상세 정보 모달 -->
        <div class="modal fade" id="ipDetailModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">IP 상세 정보</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="ipDetailContent">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </main>
</div>

<script>
function blockIP(ip) {
    if (!confirm(`IP ${ip}를 차단하시겠습니까?`)) return;
    
    fetch('/admin/tracking/block', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ip: ip})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('IP가 차단되었습니다.');
            location.reload();
        } else {
            alert(data.message || 'IP 차단에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function unblockIP(ip) {
    if (!confirm(`IP ${ip}의 차단을 해제하시겠습니까?`)) return;
    
    fetch('/admin/tracking/unblock', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ip: ip})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('IP 차단이 해제되었습니다.');
            location.reload();
        } else {
            alert(data.message || 'IP 차단 해제에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function showIPDetail(ip) {
    const modal = new bootstrap.Modal(document.getElementById('ipDetailModal'));
    modal.show();
    
    fetch(`/admin/tracking/detail/${ip}`)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('ipDetailContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>IP 정보</h6>
                        <table class="table table-sm">
                            <tr><th>IP 주소</th><td>${data.ip}</td></tr>
                            <tr><th>국가</th><td>${data.country || '-'}</td></tr>
                            <tr><th>지역</th><td>${data.region || '-'}</td></tr>
                            <tr><th>도시</th><td>${data.city || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>방문 통계</h6>
                        <table class="table table-sm">
                            <tr><th>총 방문 횟수</th><td>${data.visit_count}</td></tr>
                            <tr><th>첫 방문</th><td>${data.first_visit}</td></tr>
                            <tr><th>최근 방문</th><td>${data.last_visit}</td></tr>
                        </table>
                    </div>
                </div>
            `;
        }
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
