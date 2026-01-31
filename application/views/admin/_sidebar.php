<div class="d-flex flex-column flex-shrink-0 p-3 bg-white border-end" style="width: 280px; min-height: calc(100vh - 56px); overflow-y: auto;">
    <div class="d-flex align-items-center mb-3 mb-md-0 me-md-auto">
        <span class="fs-4 fw-bold text-main">
            <i class="fas fa-cog me-2"></i>관리자
        </span>
    </div>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <!-- 대시보드 -->
        <li class="nav-item">
            <a href="/admin" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/admin') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt me-2"></i>대시보드
            </a>
        </li>
        
        <!-- 사이트 설정 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#siteMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/config') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-cogs me-2"></i>사이트 설정</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/config') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/site') !== false) ? 'show' : ''; ?>" id="siteMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/config" class="nav-link py-1 small <?php echo ($_SERVER['REQUEST_URI'] == '/admin/config') ? 'active' : ''; ?>">기본 설정</a></li>
                    <li><a href="/admin/site/favicon" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/site/favicon') !== false) ? 'active' : ''; ?>">파비콘 설정</a></li>
                    <li><a href="/admin/site/header" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/site/header') !== false) ? 'active' : ''; ?>">헤더 코드</a></li>
                    <li><a href="/admin/site/footer" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/site/footer') !== false) ? 'active' : ''; ?>">푸터 코드</a></li>
                    <li><a href="/admin/site/rss" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/site/rss') !== false) ? 'active' : ''; ?>">RSS 설정</a></li>
                    <li><a href="/admin/site/sitemap" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/site/sitemap') !== false) ? 'active' : ''; ?>">사이트맵 설정</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 회원 관리 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#memberMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-users me-2"></i>회원 관리</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member') !== false) ? 'show' : ''; ?>" id="memberMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/member/join-config" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member/join-config') !== false) ? 'active' : ''; ?>">회원가입 설정</a></li>
                    <li><a href="/admin/members" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/members') !== false || (strpos($_SERVER['REQUEST_URI'], '/admin/member/') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/member/') === strrpos($_SERVER['REQUEST_URI'], '/admin/member/'))) ? 'active' : ''; ?>">회원 리스트</a></li>
                    <li><a href="/admin/member/levels" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member/levels') !== false) ? 'active' : ''; ?>">회원 등급</a></li>
                    <li><a href="/admin/member/points" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member/points') !== false) ? 'active' : ''; ?>">회원 포인트</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 게시판 관리 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#boardMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/board') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-list me-2"></i>게시판 관리</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/board') !== false) ? 'show' : ''; ?>" id="boardMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/boards" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/boards') !== false) ? 'active' : ''; ?>">게시판 관리</a></li>
                    <li><a href="/admin/board/posts" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/board/posts') !== false) ? 'active' : ''; ?>">게시물 리스트</a></li>
                    <li><a href="/admin/board/comments" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/board/comments') !== false) ? 'active' : ''; ?>">댓글 리스트</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 통계 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#statsMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/stat') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-chart-bar me-2"></i>통계</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/stat') !== false) ? 'show' : ''; ?>" id="statsMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/statistics" class="nav-link py-1 small <?php echo ($_SERVER['REQUEST_URI'] == '/admin/statistics') ? 'active' : ''; ?>">방문자 대시보드</a></li>
                    <li><a href="/admin/stats/visitor" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/stats/visitor') !== false) ? 'active' : ''; ?>">방문자 통계</a></li>
                    <li><a href="/admin/stats/tracking" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/stats/tracking') !== false) ? 'active' : ''; ?>">방문자 추적</a></li>
                    <li><a href="/admin/stats/posts" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/stats/posts') !== false) ? 'active' : ''; ?>">게시물 통계</a></li>
                </ul>
            </div>
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

.nav-pills .nav-link .fa-chevron-down {
    transition: transform 0.3s;
    font-size: 0.75rem;
}

.nav-pills .nav-link[aria-expanded="true"] .fa-chevron-down {
    transform: rotate(180deg);
}

.collapse .nav-link {
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.collapse .nav-link.active {
    background-color: rgba(255, 165, 15, 0.1);
    color: var(--main-color1);
}
</style>
