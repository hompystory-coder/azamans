<header>
    <nav>
        <div class="container">
            <h1><a href="/"><?php echo getConfig('site_name', 'MVC Framework'); ?></a></h1>
            <ul class="nav-menu">
                <li><a href="/">홈</a></li>
                <li><a href="/home/about">소개</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="/member/mypage">마이페이지</a></li>
                    <li><a href="/member/logout">로그아웃</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="/admin" class="admin-link">관리자</a></li>
                    <?php endif; ?>
                <?php else: ?>
                    <li><a href="/member/login">로그인</a></li>
                    <li><a href="/member/register">회원가입</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>
