<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title ?? 'MVC Framework'); ?></title>
    
    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard-dynamic-subset.css" />
    <link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet">
    
    <!-- Bootstrap 6 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 6 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <!-- Header Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                <i class="fas fa-cube me-2"></i>
                <?php echo xssFilter(getConfig('site_name', 'MVC Framework')); ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <i class="fas fa-home me-1"></i> 홈
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/home/about">
                            <i class="fas fa-info-circle me-1"></i> 소개
                        </a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <!-- 로그인 상태 -->
                        <li class="nav-item">
                            <a class="nav-link" href="/member/mypage">
                                <i class="fas fa-user me-1"></i> 마이페이지
                            </a>
                        </li>
                        
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link text-primary fw-bold" href="/admin">
                                <i class="fas fa-cog me-1"></i> 관리자
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <!-- 알림 -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display: none;">
                                    0
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                                <li class="dropdown-header d-flex justify-content-between align-items-center">
                                    <span>알림</span>
                                    <a href="#" onclick="markAllAsRead(); return false;" class="text-muted small">모두 읽음</a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <div id="notificationList" style="max-height: 300px; overflow-y: auto;">
                                    <li class="dropdown-item text-center text-muted">
                                        <small>새로운 알림이 없습니다.</small>
                                    </li>
                                </div>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/member/logout">
                                <i class="fas fa-sign-out-alt me-1"></i> 로그아웃
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- 비로그인 상태 -->
                        <li class="nav-item">
                            <a class="nav-link" href="/member/login">
                                <i class="fas fa-sign-in-alt me-1"></i> 로그인
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/member/register">
                                <i class="fas fa-user-plus me-1"></i> 회원가입
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    
    <?php if (isLoggedIn()): ?>
    <script>
    // 알림 업데이트 함수
    function updateNotifications() {
        fetch('/member/getNotifications')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.notifications) {
                    const unreadCount = data.unread_count || 0;
                    const badge = document.getElementById('notificationBadge');
                    const list = document.getElementById('notificationList');
                    
                    // 배지 업데이트
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                    
                    // 알림 목록 업데이트
                    if (data.notifications.length > 0) {
                        list.innerHTML = data.notifications.map(noti => `
                            <li>
                                <a class="dropdown-item ${noti.is_read === 'N' ? 'bg-light' : ''}" href="${noti.link || '#'}">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-${noti.type === 'comment' ? 'comment' : 'bell'} text-primary me-2 mt-1"></i>
                                        <div class="flex-grow-1">
                                            <div class="small">${noti.message}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">${noti.time_ago}</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        `).join('');
                    } else {
                        list.innerHTML = '<li class="dropdown-item text-center text-muted"><small>새로운 알림이 없습니다.</small></li>';
                    }
                }
            })
            .catch(err => console.error('알림 로드 실패:', err));
    }
    
    // 모든 알림 읽음 처리
    function markAllAsRead() {
        fetch('/member/markAllNotificationsRead', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateNotifications();
            }
        })
        .catch(err => console.error('알림 읽음 처리 실패:', err));
    }
    
    // 초기 로드 및 30초마다 업데이트
    updateNotifications();
    setInterval(updateNotifications, 30000);
    </script>
    <?php endif; ?>
</body>
</html>
