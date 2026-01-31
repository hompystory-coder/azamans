<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'MVC Framework'; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
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
                            <li><a href="/admin">관리자</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="/member/login">로그인</a></li>
                        <li><a href="/member/register">회원가입</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    
    <main class="container">
        <h1>환영합니다!</h1>
        <p><?php echo $description ?? ''; ?></p>
        
        <div class="info-box">
            <h2>시스템 정보</h2>
            <ul>
                <li>사이트명: <?php echo getConfig('site_name', 'MVC Framework'); ?></li>
                <li>현재 시간: <?php echo date('Y-m-d H:i:s'); ?></li>
                <li>서버 IP: <?php echo $_SERVER['SERVER_ADDR'] ?? 'N/A'; ?></li>
                <li>접속 IP: <?php echo getClientIP(); ?></li>
                <?php if (isLoggedIn()): ?>
                    <li>로그인: <?php echo $_SESSION['username']; ?> (레벨: <?php echo $_SESSION['level']; ?>)</li>
                <?php endif; ?>
            </ul>
        </div>
        
        <div class="features">
            <h2>주요 기능</h2>
            <div class="feature-grid">
                <div class="feature-card">
                    <h3>📝 게시판</h3>
                    <p>다양한 스타일의 게시판 지원</p>
                </div>
                <div class="feature-card">
                    <h3>👥 회원 관리</h3>
                    <p>레벨별 회원 관리 시스템</p>
                </div>
                <div class="feature-card">
                    <h3>🔐 보안</h3>
                    <p>PDO, XSS, CSRF 방어</p>
                </div>
                <div class="feature-card">
                    <h3>⚙️ 관리자</h3>
                    <p>사이트 전체 관리 기능</p>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo getConfig('site_name', 'MVC Framework'); ?>. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
