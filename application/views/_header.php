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
            
            <!-- 하단 메뉴 영역 -->
            <div class="w-100"></div> <!-- 줄바꿈 -->
            <div class="header-bottom-menu-inner">
                <?php
                // 데이터베이스에서 메뉴 불러오기
                $menus = getDbArray("
                    SELECT * FROM header_menu 
                    WHERE is_active = 'Y' AND parent_id = 0 AND is_blocked = 'N'
                    ORDER BY menu_order ASC, id ASC
                ", []) ?? [];
                
                if (!empty($menus)):
                    // 메뉴가 있을 때
                    foreach ($menus as $menu):
                        // 숨김 메뉴는 표시하지 않음
                        if ($menu['is_hidden'] === 'Y') continue;
                        
                        // URL 생성
                        $menuUrl = '#';
                        
                        if (!empty($menu['custom_url']) && $menu['use_redirect'] === 'Y') {
                            // 커스텀 URL 리다이렉트
                            $menuUrl = xssFilter($menu['custom_url']);
                        } else {
                            // 메뉴 타입에 따른 URL
                            switch ($menu['menu_type']) {
                                case 'page':
                                    $menuUrl = '/page/' . $menu['id'];
                                    break;
                                case 'board':
                                    // menu_target이 있는 경우에만 게시판 링크 생성
                                    if (!empty($menu['menu_target'])) {
                                        $menuUrl = '/bbs/' . xssFilter($menu['menu_target']);
                                    }
                                    break;
                                case 'content':
                                    $menuUrl = '/content/' . xssFilter($menu['menu_target']);
                                    break;
                            }
                        }
                        
                        // 타겟 윈도우
                        $target = $menu['target_window'] === 'blank' ? ' target="_blank"' : '';
                ?>
                    <a class="nav-link" href="<?php echo $menuUrl; ?>"<?php echo $target; ?>>
                        <?php echo xssFilter($menu['menu_name']); ?>
                    </a>
                <?php 
                    endforeach;
                else: 
                ?>
                    <!-- 메뉴가 없을 때 -->
                    <?php if (isAdmin()): ?>
                        <!-- 관리자: 메뉴 생성 바로가기 -->
                        <a class="nav-link text-muted" href="/admin/menu/header">
                            <i class="fas fa-plus-circle me-2"></i>메뉴를 생성해주세요 (바로가기)
                        </a>
                    <?php else: ?>
                        <!-- 비회원/일반회원: 안내 멘트 -->
                        <span class="text-muted" style="font-size: 0.95rem;">
                            <i class="fas fa-info-circle me-2"></i>메뉴가 없습니다
                        </span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- 검색창 -->
                <div class="header-search ms-auto">
                    <input type="text" placeholder="검색" id="headerSearchInput">
                    <button type="button" onclick="headerSearch()">검색창</button>
                </div>
            </div>
        </div>
    </nav>
    
    <script>
    function headerSearch() {
        const query = document.getElementById('headerSearchInput').value;
        if (query.trim()) {
            window.location.href = '/search?q=' + encodeURIComponent(query);
        }
    }
    
    // 엔터키로 검색
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('headerSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    headerSearch();
                }
            });
        }
    });
    </script>
    
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
