<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?= xssFilter($title) ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/admin">대시보드</a></li>
                        <li class="breadcrumb-item"><a href="/admin/members">회원 관리</a></li>
                        <li class="breadcrumb-item active">회원 상세</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <?php if (!empty($member)): ?>
        
        <!-- 회원 정보 요약 -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-lg">
                            <img src="<?= $member['profile_image'] ?? '/public/images/default-avatar.png' ?>" 
                                 class="rounded-circle" width="80" height="80">
                        </div>
                    </div>
                    <div class="col">
                        <h4 class="mb-1">
                            <?= xssFilter($member['name']) ?> 
                            <span class="badge bg-primary">Lv.<?= $member['level'] ?></span>
                            <?php if (($member['status'] ?? 'active') === 'active'): ?>
                            <span class="badge bg-success">활성</span>
                            <?php elseif ($member['status'] === 'banned'): ?>
                            <span class="badge bg-danger">차단</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">비활성</span>
                            <?php endif; ?>
                        </h4>
                        <p class="text-muted mb-1">
                            <i class="fas fa-user me-2"></i><?= xssFilter($member['user_id']) ?>
                            <i class="fas fa-envelope ms-3 me-2"></i><?= xssFilter($member['email']) ?>
                        </p>
                        <p class="text-muted mb-0">
                            <small>
                                <i class="fas fa-calendar-alt me-2"></i>가입일: <?= date('Y-m-d', strtotime($member['reg_date'])) ?>
                                <i class="fas fa-clock ms-3 me-2"></i>최근 로그인: <?= $member['last_login'] ? date('Y-m-d H:i', strtotime($member['last_login'])) : '기록 없음' ?>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 탭 메뉴 -->
        <ul class="nav nav-tabs mb-4" id="memberTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab">
                    <i class="fas fa-user me-2"></i>기본 정보
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab">
                    <i class="fas fa-chart-line me-2"></i>활동 통계
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                    <i class="fas fa-key me-2"></i>비밀번호 재설정
                </button>
            </li>
        </ul>
        
        <!-- 탭 컨텐츠 -->
        <div class="tab-content" id="memberTabContent">
            
            <!-- 기본 정보 -->
            <div class="tab-pane fade show active" id="info" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user"></i> 기본 정보
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="infoForm">
                            <input type="hidden" name="uid" value="<?= $member['uid'] ?>">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">UID</label>
                                    <input type="text" class="form-control" value="<?= $member['uid'] ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">아이디</label>
                                    <input type="text" class="form-control" value="<?= xssFilter($member['user_id']) ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">이름 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" value="<?= xssFilter($member['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">닉네임</label>
                                    <input type="text" class="form-control" name="nickname" value="<?= xssFilter($member['nickname'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">이메일 <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="<?= xssFilter($member['email']) ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">등급 <span class="text-danger">*</span></label>
                                    <select class="form-select" name="level" required>
                                        <?php
                                        $levels = getDbArray("SELECT level, level_name FROM member_level ORDER BY level ASC");
                                        foreach ($levels as $lv):
                                        ?>
                                        <option value="<?= $lv['level'] ?>" <?= $member['level'] == $lv['level'] ? 'selected' : '' ?>>
                                            Lv.<?= $lv['level'] ?> - <?= xssFilter($lv['level_name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">포인트</label>
                                    <input type="number" class="form-control" name="point" value="<?= $member['point'] ?? 0 ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">상태 <span class="text-danger">*</span></label>
                                    <select class="form-select" name="status" required>
                                        <option value="active" <?= ($member['status'] ?? 'active') == 'active' ? 'selected' : '' ?>>활성</option>
                                        <option value="pending" <?= ($member['status'] ?? 'active') == 'pending' ? 'selected' : '' ?>>대기</option>
                                        <option value="inactive" <?= ($member['status'] ?? 'active') == 'inactive' ? 'selected' : '' ?>>비활성</option>
                                        <option value="banned" <?= ($member['status'] ?? 'active') == 'banned' ? 'selected' : '' ?>>차단</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">휴대폰</label>
                                    <input type="text" class="form-control" name="phone" value="<?= xssFilter($member['phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">전화번호</label>
                                    <input type="text" class="form-control" name="tel" value="<?= xssFilter($member['tel'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">주소</label>
                                <input type="text" class="form-control" name="address" value="<?= xssFilter($member['address'] ?? '') ?>">
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="saveInfo()">
                                <i class="fas fa-save me-2"></i>저장
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- 활동 통계 -->
            <div class="tab-pane fade" id="stats" role="tabpanel">
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-file-alt fa-3x"></i>
                                </div>
                                <h3 class="mb-1"><?= number_format($stats['post_count'] ?? 0) ?></h3>
                                <p class="text-muted mb-0 small">작성 게시물</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-success mb-2">
                                    <i class="fas fa-comments fa-3x"></i>
                                </div>
                                <h3 class="mb-1"><?= number_format($stats['comment_count'] ?? 0) ?></h3>
                                <p class="text-muted mb-0 small">작성 댓글</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-warning mb-2">
                                    <i class="fas fa-coins fa-3x"></i>
                                </div>
                                <h3 class="mb-1"><?= number_format($member['point'] ?? 0) ?></h3>
                                <p class="text-muted mb-0 small">보유 포인트</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <div class="text-info mb-2">
                                    <i class="fas fa-sign-in-alt fa-3x"></i>
                                </div>
                                <h3 class="mb-1 small"><?= $member['last_login'] ? date('m/d H:i', strtotime($member['last_login'])) : '-' ?></h3>
                                <p class="text-muted mb-0 small">최근 로그인</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 최근 게시물 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> 최근 게시물
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>번호</th>
                                        <th>게시판</th>
                                        <th>제목</th>
                                        <th>작성일</th>
                                        <th>조회</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_posts)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">작성한 게시물이 없습니다.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_posts as $post): ?>
                                        <tr>
                                            <td><?= $post['uid'] ?></td>
                                            <td><span class="badge bg-secondary"><?= xssFilter($post['board_id']) ?></span></td>
                                            <td><a href="/bbs/<?= xssFilter($post['board_id']) ?>/view/<?= $post['uid'] ?>"><?= xssFilter($post['title']) ?></a></td>
                                            <td><?= date('Y-m-d', strtotime($post['created_at'])) ?></td>
                                            <td><?= number_format($post['view'] ?? 0) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- 최근 댓글 -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-comment"></i> 최근 댓글
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>번호</th>
                                        <th>게시물</th>
                                        <th>내용</th>
                                        <th>작성일</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_comments)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">작성한 댓글이 없습니다.</td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_comments as $comment): ?>
                                        <tr>
                                            <td><?= $comment['uid'] ?></td>
                                            <td><a href="/bbs/<?= xssFilter($comment['board_id']) ?>/view/<?= $comment['parent_uid'] ?>"><?= xssFilter($comment['post_title'] ?? '게시물') ?></a></td>
                                            <td><?= xssFilter(mb_substr($comment['content'], 0, 50)) . (mb_strlen($comment['content']) > 50 ? '...' : '') ?></td>
                                            <td><?= date('Y-m-d', strtotime($comment['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 비밀번호 재설정 -->
            <div class="tab-pane fade" id="password" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-key"></i> 비밀번호 재설정
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>주의:</strong> 회원의 비밀번호를 강제로 변경합니다. 변경 후 회원에게 알려주세요.
                        </div>
                        
                        <form id="passwordForm">
                            <input type="hidden" name="uid" value="<?= $member['uid'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">새 비밀번호 <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="new_password" id="new_password" 
                                       required minlength="8" placeholder="8자 이상 입력하세요">
                                <small class="text-muted">최소 8자 이상 입력해야 합니다.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password_confirm" id="password_confirm" 
                                       required minlength="8" placeholder="비밀번호를 다시 입력하세요">
                            </div>
                            
                            <button type="button" class="btn btn-danger" onclick="resetPassword()">
                                <i class="fas fa-key me-2"></i>비밀번호 재설정
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- 하단 버튼 -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="/admin/members" class="btn btn-secondary">
                            <i class="fas fa-list me-2"></i>목록으로
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger" onclick="deleteMember()">
                            <i class="fas fa-trash me-2"></i>회원 삭제
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            회원 정보를 찾을 수 없습니다.
        </div>
        <?php endif; ?>
        
    </main>
</div>

<script>
function saveInfo() {
    const form = document.getElementById('infoForm');
    const formData = new FormData(form);
    
    fetch('/admin/member/update', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('회원 정보가 저장되었습니다.');
            location.reload();
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function resetPassword() {
    const newPassword = document.getElementById('new_password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    if (newPassword.length < 8) {
        alert('비밀번호는 8자 이상이어야 합니다.');
        return;
    }
    
    if (newPassword !== passwordConfirm) {
        alert('비밀번호가 일치하지 않습니다.');
        return;
    }
    
    if (!confirm('정말 비밀번호를 재설정하시겠습니까?')) return;
    
    const formData = new FormData();
    formData.append('uid', '<?= $member['uid'] ?>');
    formData.append('new_password', newPassword);
    
    fetch('/admin/member/resetPassword', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('비밀번호가 재설정되었습니다.');
            document.getElementById('passwordForm').reset();
        } else {
            alert(data.message || '재설정에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function deleteMember() {
    if (!confirm('정말 이 회원을 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.')) return;
    
    if (!confirm('회원의 모든 게시물과 댓글도 함께 삭제됩니다.\n\n정말 진행하시겠습니까?')) return;
    
    fetch('/admin/member/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({uid: '<?= $member['uid'] ?>'})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('회원이 삭제되었습니다.');
            location.href = '/admin/members';
        } else {
            alert(data.message || '삭제에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
