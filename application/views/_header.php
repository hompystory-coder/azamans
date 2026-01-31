<header>
    <nav>
        <div class="container">
            <h1><a href="/"><?php echo getConfig('site_name', 'MVC Framework'); ?></a></h1>
            <ul class="nav-menu">
                <li><a href="/">홈</a></li>
                <li><a href="/home/about">소개</a></li>
                <?php if (isLoggedIn()): ?>
                    <!-- 알림 -->
                    <li class="nav-notification">
                        <a href="#" onclick="toggleNotifications(event)" class="notification-toggle">
                            <span class="icon">🔔</span>
                            <span class="badge" id="notifCount">0</span>
                        </a>
                        <div class="notification-dropdown" id="notificationDropdown" style="display:none;">
                            <div class="notification-header">
                                <h4>알림</h4>
                                <button onclick="markAllAsRead()" class="btn-small">모두 읽음</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <p class="loading">로딩 중...</p>
                            </div>
                            <div class="notification-footer">
                                <a href="/member/notifications">전체 알림 보기</a>
                            </div>
                        </div>
                    </li>
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
    
    <?php if (isLoggedIn()): ?>
    <script>
    // 알림 토글
    function toggleNotifications(e) {
        e.preventDefault();
        const dropdown = document.getElementById('notificationDropdown');
        if (dropdown.style.display === 'none') {
            dropdown.style.display = 'block';
            loadNotifications();
        } else {
            dropdown.style.display = 'none';
        }
    }
    
    // 알림 로드
    function loadNotifications() {
        fetch('/member/notifications/recent')
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('notificationList');
                if (data.success && data.notifications.length > 0) {
                    list.innerHTML = data.notifications.map(notif => `
                        <div class="notification-item ${notif.is_read === 'N' ? 'unread' : ''}">
                            <div class="notif-content">
                                <p>${notif.message}</p>
                                <span class="notif-time">${notif.time_ago}</span>
                            </div>
                        </div>
                    `).join('');
                    
                    // 읽지 않은 알림 개수 업데이트
                    const unreadCount = data.notifications.filter(n => n.is_read === 'N').length;
                    document.getElementById('notifCount').textContent = unreadCount;
                    document.getElementById('notifCount').style.display = unreadCount > 0 ? 'inline' : 'none';
                } else {
                    list.innerHTML = '<p class="no-data">새로운 알림이 없습니다.</p>';
                }
            })
            .catch(err => {
                console.error('알림 로드 실패:', err);
            });
    }
    
    // 모두 읽음 처리
    function markAllAsRead() {
        fetch('/member/notifications/mark-all-read', { method: 'POST' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            });
    }
    
    // 외부 클릭 시 드롭다운 닫기
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notificationDropdown');
        const toggle = document.querySelector('.notification-toggle');
        if (dropdown && !dropdown.contains(e.target) && !toggle.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
    
    // 주기적으로 알림 체크 (30초마다)
    setInterval(loadNotifications, 30000);
    
    // 초기 로드
    loadNotifications();
    </script>
    <?php endif; ?>
</header>
