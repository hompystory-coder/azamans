<?php include __DIR__ . '/../_header.php'; ?>

<main>
    <div class="container">
        <!-- Hero Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm animate__animated animate__fadeIn">
                    <div class="card-body text-center py-5">
                        <h1 class="display-4 fw-bold mb-3">
                            <i class="fas fa-cube text-main me-3"></i>
                            환영합니다!
                        </h1>
                        <p class="lead text-muted mb-4">
                            <?php echo xssFilter($description ?? 'PHP MVC Framework 기반 커뮤니티 시스템'); ?>
                        </p>
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <?php if (!isLoggedIn()): ?>
                                <a href="/member/register" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>회원가입
                                </a>
                                <a href="/member/login" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>로그인
                                </a>
                            <?php else: ?>
                                <a href="/member/mypage" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user me-2"></i>마이페이지
                                </a>
                                <?php if (isAdmin()): ?>
                                    <a href="/admin" class="btn btn-outline-primary btn-lg">
                                        <i class="fas fa-cog me-2"></i>관리자
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- System Info -->
        <div class="row g-4 mb-5">
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInUp">
                    <div class="card-body text-center">
                        <div class="display-6 text-main mb-3">
                            <i class="fas fa-server"></i>
                        </div>
                        <h5 class="card-title">사이트 이름</h5>
                        <p class="card-text text-muted">
                            <?php echo xssFilter($site_name ?? 'MVC Framework'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.1s;">
                    <div class="card-body text-center">
                        <div class="display-6 text-main mb-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h5 class="card-title">현재 시간</h5>
                        <p class="card-text text-muted">
                            <?php echo date('Y-m-d H:i:s'); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="card-body text-center">
                        <div class="display-6 text-main mb-3">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <h5 class="card-title">서버 IP</h5>
                        <p class="card-text text-muted">
                            <?php echo $_SERVER['SERVER_ADDR'] ?? 'N/A'; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInUp" style="animation-delay: 0.3s;">
                    <div class="card-body text-center">
                        <div class="display-6 text-main mb-3">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h5 class="card-title">접속 IP</h5>
                        <p class="card-text text-muted">
                            <?php echo getClientIP(); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Features -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4 fw-bold">
                    <i class="fas fa-star text-main me-2"></i>
                    주요 기능
                </h2>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInLeft">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="display-6 text-main me-3">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h5 class="card-title mb-0">게시판</h5>
                        </div>
                        <p class="card-text text-muted">
                            다양한 주제의 게시판을 통해 자유롭게 소통하세요.
                            파일 첨부, 댓글, 검색 기능을 지원합니다.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInUp">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="display-6 text-main me-3">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="card-title mb-0">회원 관리</h5>
                        </div>
                        <p class="card-text text-muted">
                            프로필 관리, 포인트 시스템, 알림 기능으로
                            편리한 회원 경험을 제공합니다.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 animate__animated animate__fadeInRight">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="display-6 text-main me-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5 class="card-title mb-0">보안</h5>
                        </div>
                        <p class="card-text text-muted">
                            XSS, CSRF, SQL Injection 방어 등
                            강력한 보안 시스템으로 안전하게 운영됩니다.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../_footer.php'; ?>
