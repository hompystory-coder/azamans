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
                    <i class="fas fa-tachometer-alt text-main me-2"></i>
                    <?php echo xssFilter($title); ?>
                </h1>
                <div class="text-muted">
                    <i class="far fa-clock me-1"></i>
                    <?php echo date('Y년 m월 d일 H:i'); ?>
                </div>
            </div>
            
            <!-- 통계 카드 -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm animate__animated animate__fadeInUp">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">전체 회원</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo number_format($stats['total_members'] ?? 0); ?></h2>
                                    <?php if (($stats['today_members'] ?? 0) > 0): ?>
                                        <small class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            오늘 +<?php echo $stats['today_members']; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="display-4 text-primary opacity-25">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">전체 게시물</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo number_format($stats['total_posts'] ?? 0); ?></h2>
                                    <?php if (($stats['today_posts'] ?? 0) > 0): ?>
                                        <small class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            오늘 +<?php echo $stats['today_posts']; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="display-4 text-success opacity-25">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">전체 댓글</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo number_format($stats['total_comments'] ?? 0); ?></h2>
                                    <?php if (($stats['today_comments'] ?? 0) > 0): ?>
                                        <small class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            오늘 +<?php echo $stats['today_comments']; ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="display-4 text-warning opacity-25">
                                    <i class="fas fa-comments"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">오늘 신규</h6>
                                    <h2 class="mb-0 fw-bold"><?php echo number_format($stats['today_members'] ?? 0); ?></h2>
                                    <small class="text-muted">신규 회원</small>
                                </div>
                                <div class="display-4 text-danger opacity-25">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <!-- 최근 회원 -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInLeft">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-user-clock text-main me-2"></i>최근 가입 회원
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>아이디</th>
                                            <th>이름</th>
                                            <th>등급</th>
                                            <th>가입일</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_members)): ?>
                                            <?php foreach ($recent_members as $member): ?>
                                            <tr>
                                                <td>
                                                    <a href="/admin/member/<?php echo $member['uid']; ?>" class="text-decoration-none">
                                                        <?php echo xssFilter($member['user_id']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo xssFilter($member['name']); ?></td>
                                                <td><span class="badge bg-primary">Lv.<?php echo $member['level']; ?></span></td>
                                                <td class="text-muted small"><?php echo date('Y-m-d', strtotime($member['reg_date'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">데이터가 없습니다.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-2 text-center">
                            <a href="/admin/members" class="text-main text-decoration-none small fw-bold">
                                전체 보기 <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- 최근 게시물 -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInRight">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-file-alt text-main me-2"></i>최근 게시물
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>게시판</th>
                                            <th>제목</th>
                                            <th>작성자</th>
                                            <th>작성일</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recent_posts)): ?>
                                            <?php foreach ($recent_posts as $post): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?php echo xssFilter($post['board_id']); ?></span></td>
                                                <td>
                                                    <a href="/bbs/<?php echo $post['board_id']; ?>/view/<?php echo $post['uid']; ?>" 
                                                       class="text-decoration-none" target="_blank">
                                                        <?php echo xssFilter(mb_substr($post['subject'], 0, 20)) . (mb_strlen($post['subject']) > 20 ? '...' : ''); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo xssFilter($post['writer']); ?></td>
                                                <td class="text-muted small"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">데이터가 없습니다.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-2 text-center">
                            <a href="/admin/boards" class="text-main text-decoration-none small fw-bold">
                                전체 보기 <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
