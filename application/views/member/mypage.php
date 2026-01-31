<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>마이페이지</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/mypage.css">
</head>
<body>
    <?php include __DIR__ . '/_header.php'; ?>
    
    <main class="container mypage-container">
        <h1>마이페이지</h1>
        
        <div class="mypage-grid">
            <!-- 프로필 카드 -->
            <div class="profile-card">
                <div class="profile-image-wrapper">
                    <img src="<?php echo $user['profile_image'] ?: '/public/images/default-avatar.png'; ?>" 
                         alt="프로필 사진" class="profile-image" id="profileImg">
                    <button type="button" class="btn-change-photo" onclick="document.getElementById('photoInput').click()">
                        📷 사진 변경
                    </button>
                    <form id="photoForm" style="display: none;">
                        <input type="file" id="photoInput" accept="image/*" onchange="uploadPhoto(event)">
                    </form>
                </div>
                
                <div class="profile-info">
                    <h2><?php echo xssFilter($user['name'] ?? $user['user_id']); ?></h2>
                    <p class="user-id">@<?php echo xssFilter($user['user_id']); ?></p>
                    <p class="user-email"><?php echo xssFilter($user['email']); ?></p>
                    
                    <div class="user-badge">
                        <span class="badge">Lv.<?php echo $user['level']; ?></span>
                        <?php if ($user['level'] >= 9): ?>
                            <span class="badge badge-admin">관리자</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="user-stats">
                        <div class="stat-item">
                            <span class="stat-label">포인트</span>
                            <span class="stat-value">💰 <?php echo number_format($user['point'] ?? 0); ?>P</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">게시물</span>
                            <span class="stat-value"><?php echo number_format($user['post_count'] ?? 0); ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">댓글</span>
                            <span class="stat-value"><?php echo number_format($user['comment_count'] ?? 0); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 회원 정보 수정 -->
            <div class="info-panel">
                <h3>회원 정보</h3>
                <form id="updateForm" onsubmit="updateProfile(event)">
                    <div class="form-group">
                        <label>아이디</label>
                        <input type="text" value="<?php echo xssFilter($user['user_id']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">이름</label>
                        <input type="text" name="name" id="name" 
                               value="<?php echo xssFilter($user['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="nickname">닉네임</label>
                        <input type="text" name="nickname" id="nickname" 
                               value="<?php echo xssFilter($user['nickname'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">이메일</label>
                        <input type="email" name="email" id="email" 
                               value="<?php echo xssFilter($user['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">전화번호</label>
                        <input type="tel" name="phone" id="phone" 
                               value="<?php echo xssFilter($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">정보 수정</button>
                </form>
            </div>
            
            <!-- 비밀번호 변경 -->
            <div class="info-panel">
                <h3>비밀번호 변경</h3>
                <form id="passwordForm" onsubmit="changePassword(event)">
                    <div class="form-group">
                        <label for="current_password">현재 비밀번호</label>
                        <input type="password" name="current_password" id="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">새 비밀번호</label>
                        <input type="password" name="new_password" id="new_password" 
                               minlength="8" required>
                        <p class="form-help">8자 이상 입력해주세요</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password_confirm">새 비밀번호 확인</label>
                        <input type="password" name="new_password_confirm" 
                               id="new_password_confirm" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">비밀번호 변경</button>
                </form>
            </div>
            
            <!-- 활동 내역 -->
            <div class="activity-panel">
                <h3>최근 활동</h3>
                
                <div class="activity-tabs">
                    <button class="tab-btn active" onclick="showTab('posts')">내 게시물</button>
                    <button class="tab-btn" onclick="showTab('comments')">내 댓글</button>
                    <button class="tab-btn" onclick="showTab('points')">포인트 내역</button>
                </div>
                
                <div id="posts" class="tab-content active">
                    <p class="loading">게시물 로딩 중...</p>
                </div>
                
                <div id="comments" class="tab-content">
                    <p class="loading">댓글 로딩 중...</p>
                </div>
                
                <div id="points" class="tab-content">
                    <p class="loading">포인트 내역 로딩 중...</p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/_footer.php'; ?>
    
    <script>
    // 프로필 사진 업로드
    function uploadPhoto(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        // 파일 타입 체크
        if (!file.type.startsWith('image/')) {
            alert('이미지 파일만 업로드 가능합니다.');
            return;
        }
        
        // 파일 크기 체크 (5MB)
        if (file.size > 5242880) {
            alert('파일 크기는 5MB 이하여야 합니다.');
            return;
        }
        
        const formData = new FormData();
        formData.append('profile_image', file);
        
        fetch('/member/uploadProfileImage', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('profileImg').src = data.image_url;
                alert(data.message);
            } else {
                alert(data.message || '프로필 사진 업로드에 실패했습니다.');
            }
        })
        .catch(err => {
            alert('사진 업로드 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    
    // 회원 정보 수정
    function updateProfile(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        fetch('/member/updateProfile', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        })
        .catch(err => {
            alert('정보 수정 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    
    // 비밀번호 변경
    function changePassword(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('new_password_confirm');
        
        if (newPassword !== confirmPassword) {
            alert('새 비밀번호가 일치하지 않습니다.');
            return;
        }
        
        // updateProfile에 비밀번호 정보 추가
        const profileData = new FormData();
        profileData.append('current_password', formData.get('current_password'));
        profileData.append('new_password', newPassword);
        profileData.append('name', document.getElementById('name').value);
        profileData.append('nickname', document.getElementById('nickname').value);
        profileData.append('phone', document.getElementById('phone').value);
        
        fetch('/member/updateProfile', {
            method: 'POST',
            body: profileData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                e.target.reset();
            }
        })
        .catch(err => {
            alert('비밀번호 변경 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    
    // 탭 전환
    function showTab(tabName) {
        // 탭 버튼 활성화
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // 탭 컨텐츠 표시
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(tabName).classList.add('active');
        
        // 데이터 로드
        loadTabData(tabName);
    }
    
    // 탭 데이터 로드
    function loadTabData(tabName) {
        const container = document.getElementById(tabName);
        container.innerHTML = '<p class="loading">로딩 중...</p>';
        
        // TODO: 실제 데이터 로드 구현
        setTimeout(() => {
            container.innerHTML = '<p class="no-data">데이터가 없습니다.</p>';
        }, 500);
    }
    
    // 초기 로드
    window.addEventListener('load', function() {
        loadTabData('posts');
    });
    </script>
</body>
</html>
