<div class="d-flex flex-column flex-shrink-0 p-3 bg-white border-end" style="width: 280px; min-height: calc(100vh - 56px);">
    <div class="d-flex align-items-center mb-3 mb-md-0 me-md-auto">
        <span class="fs-4 fw-bold text-main">
            <i class="fas fa-cog me-2"></i>관리자
        </span>
    </div>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="/admin" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/admin') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i>대시보드
            </a>
        </li>
        <li>
            <a href="/admin/config" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/config') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-cogs me-2"></i>사이트 설정
            </a>
        </li>
        <li>
            <a href="/admin/members" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-users me-2"></i>회원 관리
            </a>
        </li>
        <li>
            <a href="/admin/boards" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/board') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-list me-2"></i>게시판 관리
            </a>
        </li>
        <li>
            <a href="/admin/statistics" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/statistics') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar me-2"></i>통계
            </a>
        </li>
    </ul>
    <hr>
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
            <img src="<?php echo $_SESSION['profile_image'] ?? '/public/images/default-avatar.png'; ?>" 
                 width="32" height="32" class="rounded-circle me-2">
            <strong><?php echo xssFilter($_SESSION['username'] ?? 'Admin'); ?></strong>
        </a>
        <ul class="dropdown-menu text-small shadow">
            <li><a class="dropdown-item" href="/"><i class="fas fa-home me-2"></i>사이트로 돌아가기</a></li>
            <li><a class="dropdown-item" href="/member/mypage"><i class="fas fa-user me-2"></i>마이페이지</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/member/logout"><i class="fas fa-sign-out-alt me-2"></i>로그아웃</a></li>
        </ul>
    </div>
</div>

<style>
.nav-pills .nav-link {
    border-radius: 0.375rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.nav-pills .nav-link:hover {
    background-color: #f8f9fa;
    color: var(--main-color1);
}

.nav-pills .nav-link.active {
    background-color: var(--main-color1);
    color: white;
}

.nav-pills .nav-link i {
    width: 20px;
    text-align: center;
}
</style>
