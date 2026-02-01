<?php include __DIR__ . '/../_header.php'; ?>

<div class="container my-5">
    <div class="row">
        <!-- 왼쪽: 프로필 카드 -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="mb-3 position-relative d-inline-block">
                        <img src="<?= $user['profile_image'] ?? '/public/images/default-avatar.png' ?>" 
                             alt="프로필" 
                             class="rounded-circle border border-3 border-primary"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <button class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0" 
                                onclick="document.getElementById('profileImageInput').click()"
                                title="프로필 사진 변경">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    
                    <form id="photoForm" enctype="multipart/form-data" style="display: none;">
                        <input type="file" id="profileImageInput" name="profile_image" 
                               accept="image/*" onchange="uploadPhoto()">
                    </form>
                    
                    <h4 class="mb-1 fw-bold"><?= xssFilter($user['name']) ?></h4>
                    <p class="text-muted mb-2">@<?= xssFilter($user['user_id']) ?></p>
                    <p class="text-muted small mb-3"><?= xssFilter($user['email']) ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-primary">Lv.<?= $user['level'] ?></span>
                        <?php if ($user['level'] >= 9): ?>
                            <span class="badge bg-danger">관리자</span>
                        <?php endif; ?>
                    </div>
                    
                    <hr>
                    
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <div class="text-primary fw-bold fs-5"><?= number_format($user['point'] ?? 0) ?></div>
                            <div class="small text-muted">포인트</div>
                        </div>
                        <div class="col-4">
                            <div class="text-primary fw-bold fs-5"><?= number_format($user['post_count'] ?? 0) ?></div>
                            <div class="small text-muted">게시물</div>
                        </div>
                        <div class="col-4">
                            <div class="text-primary fw-bold fs-5"><?= number_format($user['comment_count'] ?? 0) ?></div>
                            <div class="small text-muted">댓글</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 오른쪽: 탭 메뉴 -->
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <!-- 탭 헤더 -->
                <div class="card-header bg-white">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $active_tab === 'profile' ? 'active' : '' ?>" 
                               href="/member/mypage/profile">
                                <i class="fas fa-user-edit me-2"></i>프로필 수정
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $active_tab === 'password' ? 'active' : '' ?>" 
                               href="/member/mypage/password">
                                <i class="fas fa-lock me-2"></i>비밀번호 변경
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $active_tab === 'posts' ? 'active' : '' ?>" 
                               href="/member/mypage/posts">
                                <i class="fas fa-file-alt me-2"></i>내 게시물
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $active_tab === 'comments' ? 'active' : '' ?>" 
                               href="/member/mypage/comments">
                                <i class="fas fa-comment me-2"></i>내 댓글
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= $active_tab === 'points' ? 'active' : '' ?>" 
                               href="/member/mypage/points">
                                <i class="fas fa-coins me-2"></i>포인트 내역
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- 탭 내용 -->
                <div class="card-body">
                    <?php if ($active_tab === 'profile'): ?>
                        <!-- 프로필 수정 -->
                        <h5 class="mb-4"><i class="fas fa-user-edit text-primary me-2"></i>프로필 수정</h5>
                        <form id="profileForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">이름 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" 
                                           value="<?= xssFilter($user['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">닉네임</label>
                                    <input type="text" class="form-control" name="nickname" 
                                           value="<?= xssFilter($user['nickname'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">이메일 <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= xssFilter($user['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">휴대폰번호</label>
                                    <input type="tel" class="form-control" name="phone" 
                                           value="<?= xssFilter($user['phone'] ?? '') ?>" 
                                           placeholder="010-1234-5678">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">연락처</label>
                                    <input type="tel" class="form-control" name="tel" 
                                           value="<?= xssFilter($user['tel'] ?? '') ?>" 
                                           placeholder="02-1234-5678">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">주소</label>
                                    <input type="text" class="form-control" name="address" 
                                           value="<?= xssFilter($user['address'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>저장
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                    <?php elseif ($active_tab === 'password'): ?>
                        <!-- 비밀번호 변경 -->
                        <h5 class="mb-4"><i class="fas fa-lock text-primary me-2"></i>비밀번호 변경</h5>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            비밀번호는 8자 이상이어야 합니다.
                        </div>
                        <form id="passwordForm">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">현재 비밀번호 <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">새 비밀번호 <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" name="new_password" 
                                           minlength="8" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
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
                        
                    <?php elseif ($active_tab === 'posts'): ?>
                        <!-- 내 게시물 -->
                        <h5 class="mb-4"><i class="fas fa-file-alt text-primary me-2"></i>내 게시물</h5>
                        <div id="postsContent">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($active_tab === 'comments'): ?>
                        <!-- 내 댓글 -->
                        <h5 class="mb-4"><i class="fas fa-comment text-primary me-2"></i>내 댓글</h5>
                        <div id="commentsContent">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        
                    <?php elseif ($active_tab === 'points'): ?>
                        <!-- 포인트 내역 -->
                        <h5 class="mb-4"><i class="fas fa-coins text-primary me-2"></i>포인트 내역</h5>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            현재 보유 포인트: <strong class="text-primary"><?= number_format($user['point'] ?? 0) ?></strong>점
                        </div>
                        <div id="pointsContent">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.nav-tabs .nav-link {
    color: #666;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.nav-tabs .nav-link:hover {
    color: #ffa50f;
    border-bottom-color: #ffa50f;
}

.nav-tabs .nav-link.active {
    color: #ffa50f;
    background: none;
    border-bottom-color: #ffa50f;
    font-weight: bold;
}

.border-primary {
    border-color: #ffa50f !important;
}

.text-primary, .btn-primary {
    color: #ffa50f !important;
}

.btn-primary {
    background-color: #ffa50f;
    border-color: #ffa50f;
}

.btn-primary:hover {
    background-color: #ff8c00;
    border-color: #ff8c00;
}

.badge.bg-primary {
    background-color: #ffa50f !important;
}

.list-group-item-action:hover {
    background-color: #fff5e6;
}
</style>

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
<?php if ($active_tab === 'profile'): ?>
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
    })
    .catch(err => {
        console.error(err);
        alert('오류가 발생했습니다.');
    });
});
<?php endif; ?>

// 비밀번호 변경
<?php if ($active_tab === 'password'): ?>
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
    })
    .catch(err => {
        console.error(err);
        alert('오류가 발생했습니다.');
    });
});
<?php endif; ?>

// 활동 내역 로드
<?php if (in_array($active_tab, ['posts', 'comments', 'points'])): ?>
fetch('/member/activity/<?= $active_tab ?>')
    .then(res => res.text())
    .then(html => {
        document.getElementById('<?= $active_tab ?>Content').innerHTML = html;
    })
    .catch(err => {
        console.error(err);
        document.getElementById('<?= $active_tab ?>Content').innerHTML = 
            '<div class="alert alert-danger">데이터 로드 실패</div>';
    });
<?php endif; ?>
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
