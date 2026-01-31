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
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0">
                    <i class="fas fa-users text-main me-2"></i>
                    <?php echo xssFilter($title); ?>
                </h1>
                <div class="text-muted">
                    <i class="far fa-clock me-1"></i>
                    <?php echo date('Y년 m월 d일 H:i'); ?>
                </div>
            </div>
            
            <!-- 통계 카드 -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">전체 회원</small>
                                    <h4 class="mb-0 fw-bold"><?php echo number_format($total ?? 0); ?></h4>
                                </div>
                                <i class="fas fa-users fa-2x text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">활성 회원</small>
                                    <h4 class="mb-0 fw-bold text-success">
                                        <?php 
                                        $active = 0;
                                        foreach ($members as $m) {
                                            if ($m['status'] == 'active') $active++;
                                        }
                                        echo number_format($active); 
                                        ?>
                                    </h4>
                                </div>
                                <i class="fas fa-user-check fa-2x text-success opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">차단 회원</small>
                                    <h4 class="mb-0 fw-bold text-danger">
                                        <?php 
                                        $banned = 0;
                                        foreach ($members as $m) {
                                            if ($m['status'] == 'banned') $banned++;
                                        }
                                        echo number_format($banned); 
                                        ?>
                                    </h4>
                                </div>
                                <i class="fas fa-user-slash fa-2x text-danger opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">현재 페이지</small>
                                    <h4 class="mb-0 fw-bold"><?php echo $current_page; ?> / <?php echo $total_pages; ?></h4>
                                </div>
                                <i class="fas fa-file-alt fa-2x text-info opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 검색/필터 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="/admin/members" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small">상태</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">전체 상태</option>
                                <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>활성</option>
                                <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>비활성</option>
                                <option value="banned" <?php echo (isset($_GET['status']) && $_GET['status'] == 'banned') ? 'selected' : ''; ?>>차단</option>
                                <option value="withdrawn" <?php echo (isset($_GET['status']) && $_GET['status'] == 'withdrawn') ? 'selected' : ''; ?>>탈퇴</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">등급</label>
                            <select name="level" class="form-select form-select-sm">
                                <option value="">전체 등급</option>
                                <option value="1" <?php echo (isset($_GET['level']) && $_GET['level'] == '1') ? 'selected' : ''; ?>>일반(1)</option>
                                <option value="5" <?php echo (isset($_GET['level']) && $_GET['level'] == '5') ? 'selected' : ''; ?>>정회원(5)</option>
                                <option value="9" <?php echo (isset($_GET['level']) && $_GET['level'] == '9') ? 'selected' : ''; ?>>관리자(9)</option>
                                <option value="10" <?php echo (isset($_GET['level']) && $_GET['level'] == '10') ? 'selected' : ''; ?>>최고관리자(10)</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">검색</label>
                            <input type="text" 
                                   name="search" 
                                   class="form-control form-control-sm"
                                   placeholder="아이디, 이름, 이메일 검색..." 
                                   value="<?php echo isset($_GET['search']) ? xssFilter($_GET['search']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-search me-1"></i>검색
                            </button>
                            <a href="/admin/members" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-redo me-1"></i>초기화
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 회원 목록 -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">번호</th>
                                    <th>아이디</th>
                                    <th>이름</th>
                                    <th>이메일</th>
                                    <th width="100">등급</th>
                                    <th width="100">포인트</th>
                                    <th width="100">상태</th>
                                    <th width="150">최근 로그인</th>
                                    <th width="120">가입일</th>
                                    <th width="100">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($members)): ?>
                                    <?php 
                                    $num = $total - (($current_page - 1) * 20);
                                    foreach ($members as $member): 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $num--; ?></td>
                                        <td>
                                            <a href="/admin/member/<?php echo $member['uid']; ?>" class="text-decoration-none fw-bold">
                                                <?php echo xssFilter($member['user_id']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo xssFilter($member['name']); ?></td>
                                        <td><small class="text-muted"><?php echo xssFilter($member['email']); ?></small></td>
                                        <td class="text-center">
                                            <span class="badge <?php 
                                                echo $member['level'] >= 9 ? 'bg-danger' : 
                                                    ($member['level'] >= 5 ? 'bg-success' : 'bg-secondary'); 
                                            ?>">
                                                Lv.<?php echo $member['level']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?php echo number_format($member['point'] ?? 0); ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $statusBadge = [
                                                'active' => ['bg-success', '활성'],
                                                'inactive' => ['bg-secondary', '비활성'],
                                                'banned' => ['bg-danger', '차단'],
                                                'withdrawn' => ['bg-dark', '탈퇴']
                                            ];
                                            $badge = $statusBadge[$member['status']] ?? ['bg-secondary', '알 수 없음'];
                                            ?>
                                            <span class="badge <?php echo $badge[0]; ?>"><?php echo $badge[1]; ?></span>
                                        </td>
                                        <td class="text-center small text-muted">
                                            <?php echo $member['last_login'] ? date('Y-m-d H:i', strtotime($member['last_login'])) : '-'; ?>
                                        </td>
                                        <td class="text-center small text-muted">
                                            <?php echo date('Y-m-d', strtotime($member['reg_date'])); ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="/admin/member/<?php echo $member['uid']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            회원 데이터가 없습니다.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- 페이지네이션 -->
                <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white border-0 py-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-center mb-0">
                            <?php
                            $query_params = [];
                            if (isset($_GET['search'])) $query_params[] = 'search=' . urlencode($_GET['search']);
                            if (isset($_GET['status'])) $query_params[] = 'status=' . $_GET['status'];
                            if (isset($_GET['level'])) $query_params[] = 'level=' . $_GET['level'];
                            $query_string = !empty($query_params) ? '&' . implode('&', $query_params) : '';
                            
                            // 이전 버튼
                            if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($current_page - 1) . $query_string; ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif;
                            
                            // 페이지 번호
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i . $query_string; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor;
                            
                            // 다음 버튼
                            if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($current_page + 1) . $query_string; ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
