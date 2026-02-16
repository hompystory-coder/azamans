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
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/config') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/favicon') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/headercode') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/footercode') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/rss') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/sitemap') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/seo') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/bot') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-cogs me-2"></i>사이트 설정</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/config') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/favicon') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/headercode') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/footercode') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/rss') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/sitemap') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/seo') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/bot') !== false) ? 'show' : ''; ?>" id="siteMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/config" class="nav-link py-1 small <?php echo ($_SERVER['REQUEST_URI'] == '/admin/config') ? 'active' : ''; ?>">기본 설정</a></li>
                    <li><a href="/admin/favicon" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/favicon') !== false) ? 'active' : ''; ?>">파비콘 설정</a></li>
                    <li><a href="/admin/headercode" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/headercode') !== false) ? 'active' : ''; ?>">헤더 코드</a></li>
                    <li><a href="/admin/footercode" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/footercode') !== false) ? 'active' : ''; ?>">푸터 코드</a></li>
                    <li><a href="/admin/rss" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/rss') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/sitemap') === false) ? 'active' : ''; ?>">RSS 설정</a></li>
                    <li><a href="/admin/sitemap" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/sitemap') !== false) ? 'active' : ''; ?>">Sitemap 설정</a></li>
                    <li><a href="/admin/seo" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/seo') !== false) ? 'active' : ''; ?>">SEO 설정</a></li>
                    <li><a href="/admin/bot" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/bot') !== false) ? 'active' : ''; ?>">BOT 설정</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 메뉴 관리 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#menuManageMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/menu') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-bars me-2"></i>메뉴 관리</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/menu') !== false) ? 'show' : ''; ?>" id="menuManageMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/menu/header" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/menu/header') !== false) ? 'active' : ''; ?>">헤더메뉴</a></li>
                    <li><a href="/admin/menu/footer" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/menu/footer') !== false) ? 'active' : ''; ?>">푸터메뉴</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 회원 관리 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#memberMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/joinconfig') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/levels') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/points') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-users me-2"></i>회원 관리</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/member') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/joinconfig') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/levels') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/points') !== false) ? 'show' : ''; ?>" id="memberMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/joinconfig" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/joinconfig') !== false) ? 'active' : ''; ?>">회원가입 설정</a></li>
                    <li><a href="/admin/members" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/members') !== false || (strpos($_SERVER['REQUEST_URI'], '/admin/member/') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/member/') === strrpos($_SERVER['REQUEST_URI'], '/admin/member/'))) ? 'active' : ''; ?>">회원 리스트</a></li>
                    <li><a href="/admin/levels" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/levels') !== false) ? 'active' : ''; ?>">회원 등급</a></li>
                    <li><a href="/admin/points" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/points') !== false) ? 'active' : ''; ?>">회원 포인트</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 뉴스 관리 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#newsMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/news') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-newspaper me-2"></i>뉴스 관리</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/news') !== false) ? 'show' : ''; ?>" id="newsMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/news" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/news') !== false) ? 'active' : ''; ?>">뉴스 관리</a></li>
                    <li><a href="/admin/newsposts" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/newsposts') !== false) ? 'active' : ''; ?>">뉴스 리스트</a></li>
                    <li><a href="/admin/newscomments" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/newscomments') !== false) ? 'active' : ''; ?>">댓글 관리</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 게시판 관리 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#boardMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/board') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/posts') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/comments') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-list me-2"></i>게시판 관리</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/board') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/posts') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/comments') !== false) ? 'show' : ''; ?>" id="boardMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/boards" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/boards') !== false) ? 'active' : ''; ?>">게시판 관리</a></li>
                    <li><a href="/admin/posts" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/posts') !== false) ? 'active' : ''; ?>">게시물 리스트</a></li>
                    <li><a href="/admin/comments" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/comments') !== false) ? 'active' : ''; ?>">댓글 리스트</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 플러그인 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#pluginMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/plugin') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-plug me-2"></i>플러그인</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/plugin') !== false) ? 'show' : ''; ?>" id="pluginMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/plugin/autopost" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/plugin/autopost') !== false) ? 'active' : ''; ?>">자동포스팅</a></li>
                    <li><a href="/admin/plugin/videocreate" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/plugin/videocreate') !== false) ? 'active' : ''; ?>">동영상생성</a></li>
                    <li><a href="/admin/plugin/trendposting" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/plugin/trendposting') !== false) ? 'active' : ''; ?>">트렌드포스팅</a></li>
                </ul>
            </div>
        </li>
        
        <!-- 통계 -->
        <li>
            <a href="#" class="nav-link d-flex justify-content-between align-items-center" 
               data-bs-toggle="collapse" data-bs-target="#statsMenu" 
               aria-expanded="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/statistics') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/visitor') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/tracking') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/poststats') !== false) ? 'true' : 'false'; ?>">
                <span><i class="fas fa-chart-bar me-2"></i>통계</span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="collapse <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/statistics') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/visitor') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/tracking') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/poststats') !== false) ? 'show' : ''; ?>" id="statsMenu">
                <ul class="nav flex-column ms-3">
                    <li><a href="/admin/statistics" class="nav-link py-1 small <?php echo ($_SERVER['REQUEST_URI'] == '/admin/statistics') ? 'active' : ''; ?>">방문자 대시보드</a></li>
                    <li><a href="/admin/visitor" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/visitor') !== false) ? 'active' : ''; ?>">방문자 통계</a></li>
                    <li><a href="/admin/tracking" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/tracking') !== false) ? 'active' : ''; ?>">방문자 추적</a></li>
                    <li><a href="/admin/poststats" class="nav-link py-1 small <?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/poststats') !== false) ? 'active' : ''; ?>">게시물 통계</a></li>
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

<script>
// 다른 메뉴 클릭 시 열린 서브메뉴 닫기
document.addEventListener('DOMContentLoaded', function() {
    // 모든 collapse 토글 버튼
    const collapseToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
    
    collapseToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const targetId = this.getAttribute('data-bs-target');
            
            // 모든 collapse 메뉴 찾기
            const allCollapses = document.querySelectorAll('.collapse.show');
            
            // 현재 클릭한 메뉴가 아닌 다른 열린 메뉴들 닫기
            allCollapses.forEach(collapse => {
                if ('#' + collapse.id !== targetId) {
                    const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    });
    
    // 대시보드 클릭 시 모든 서브메뉴 닫기
    const dashboardLink = document.querySelector('a[href="/admin"]');
    if (dashboardLink) {
        dashboardLink.addEventListener('click', function() {
            const allCollapses = document.querySelectorAll('.collapse.show');
            allCollapses.forEach(collapse => {
                const bsCollapse = bootstrap.Collapse.getInstance(collapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            });
        });
    }
});
</script>
