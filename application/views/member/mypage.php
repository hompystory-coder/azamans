<?php include __DIR__ . '/../_header.php'; ?>

<main>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <!-- 프로필 카드 -->
                <div class="card border-0 shadow-sm animate__animated animate__fadeInLeft">
                    <div class="card-body text-center py-5">
                        <div class="mb-3 position-relative d-inline-block">
                            <img src="<?php echo $user['profile_image'] ?? '/public/images/default-avatar.png'; ?>" 
                                 alt="프로필" 
                                 class="rounded-circle border border-3 border-main"
                                 style="width: 120px; height: 120px; object-fit: cover;">
                            <button class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" 
                                    onclick="document.getElementById('profileImageInput').click()">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        
                        <form id="photoForm" enctype="multipart/form-data" style="display: none;">
                            <input type="file" id="profileImageInput" name="profile_image" 
                                   accept="image/*" onchange="uploadPhoto()">
                        </form>
                        
                        <h3 class="mb-1 fw-bold"><?php echo xssFilter($user['name']); ?></h3>
                        <p class="text-muted mb-2">@<?php echo xssFilter($user['user_id']); ?></p>
                        <p class="text-muted small mb-3"><?php echo xssFilter($user['email']); ?></p>
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <span class="badge bg-primary">Lv.<?php echo $user['level']; ?></span>
                            <?php if ($user['level'] >= 9): ?>
                                <span class="badge bg-danger">관리자</span>
                            <?php endif; ?>
                        </div>
                        
                        <hr>
                        
                        <div class="row text-center g-3">
                            <div class="col-4">
                                <div class="text-main fw-bold fs-4"><?php echo number_format($user['point'] ?? 0); ?></div>
                                <div class="small text-muted">포인트</div>
                            </div>
                            <div class="col-4">
                                <div class="text-main fw-bold fs-4"><?php echo number_format($user['post_count'] ?? 0); ?></div>
                                <div class="small text-muted">게시물</div>
                            </div>
                            <div class="col-4">
                                <div class="text-main fw-bold fs-4"><?php echo number_format($user['comment_count'] ?? 0); ?></div>
                                <div class="small text-muted">댓글</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <!-- 프로필 수정 -->
                <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeInRight">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-user-edit text-main me-2"></i>프로필 수정
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="profileForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">이름</label>
                                    <input type="text" class="form-control" name="name" 
                                           value="<?php echo xssFilter($user['name']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">닉네임</label>
                                    <input type="text" class="form-control" name="nickname" 
                                           value="<?php echo xssFilter($user['nickname'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">이메일</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?php echo xssFilter($user['email']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">전화번호</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?php echo xssFilter($user['phone'] ?? ''); ?>">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>저장
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- 비밀번호 변경 -->
                <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeInRight" style="animation-delay: 0.1s;">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-lock text-main me-2"></i>비밀번호 변경
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="passwordForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">현재 비밀번호</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">새 비밀번호</label>
                                    <input type="password" class="form-control" name="new_password" 
                                           minlength="8" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">비밀번호 확인</label>
                                    <input type="password" class="form-control" name="new_password_confirm" 
                                           minlength="8" required>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>비밀번호 변경
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- 활동 내역 -->
                <div class="card border-0 shadow-sm animate__animated animate__fadeInRight" style="animation-delay: 0.2s;">
                    <div class="card-header bg-white border-0 py-3">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#posts" 
                                        onclick="loadTabData('posts')" type="button">
                                    <i class="fas fa-file-alt me-1"></i>내 게시물
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#comments" 
                                        onclick="loadTabData('comments')" type="button">
                                    <i class="fas fa-comment me-1"></i>내 댓글
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#points" 
                                        onclick="loadTabData('points')" type="button">
                                    <i class="fas fa-coins me-1"></i>포인트 내역
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="posts" role="tabpanel">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-main" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="comments" role="tabpanel"></div>
                            <div class="tab-pane fade" id="points" role="tabpanel"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// 프로필 사진 업로드
function uploadPhoto() {
    const formData = new FormData(document.getElementById('photoForm'));
    
    fetch('/member/uploadProfileImage', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('프로필 사진이 업데이트되었습니다.');
            location.reload();
        } else {
            alert(data.message || '업로드 실패');
        }
    })
    .catch(err => {
        console.error('Upload error:', err);
        alert('업로드 중 오류가 발생했습니다.');
    });
}

// 프로필 수정
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/member/updateProfile', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('프로필이 업데이트되었습니다.');
            location.reload();
        } else {
            alert(data.message || '업데이트 실패');
        }
    });
});

// 비밀번호 변경
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    if (data.new_password !== data.new_password_confirm) {
        alert('새 비밀번호가 일치하지 않습니다.');
        return;
    }
    
    fetch('/member/changePassword', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('비밀번호가 변경되었습니다.');
            this.reset();
        } else {
            alert(data.message || '변경 실패');
        }
    });
});

// 탭 데이터 로드
function loadTabData(tab) {
    const container = document.getElementById(tab);
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-main"></div></div>';
    
    fetch(`/member/activity/${tab}`)
        .then(res => res.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(err => {
            container.innerHTML = '<div class="alert alert-danger">데이터 로드 실패</div>';
        });
}

// 초기 로드
loadTabData('posts');
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
