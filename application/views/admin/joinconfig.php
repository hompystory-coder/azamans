<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?= xssFilter($title) ?></h2>
                <p class="text-muted mb-0"><?= date('Y년 m월 d일 H:i') ?></p>
            </div>
        </div>
        
        <!-- 탭 메뉴 -->
        <ul class="nav nav-tabs mb-4" id="joinConfigTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="env-tab" data-bs-toggle="tab" data-bs-target="#env" type="button" role="tab">
                    <i class="fas fa-cog me-2"></i>가입 환경
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms" type="button" role="tab">
                    <i class="fas fa-file-contract me-2"></i>이용약관
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab">
                    <i class="fas fa-shield-alt me-2"></i>개인정보보호정책
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="youth-tab" data-bs-toggle="tab" data-bs-target="#youth" type="button" role="tab">
                    <i class="fas fa-child me-2"></i>청소년보호정책
                </button>
            </li>
        </ul>
        
        <!-- 탭 컨텐츠 -->
        <div class="tab-content" id="joinConfigTabContent">
            
            <!-- 가입 환경 -->
            <div class="tab-pane fade show active" id="env" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-cog"></i> 가입 환경 설정
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="envForm">
                            <!-- 회원 가입 사용 여부 -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">회원 가입 허용</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="use_join" id="use_join_y" value="Y" <?= ($join_config['use_join'] ?? 'Y') == 'Y' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="use_join_y">
                                        허용 (회원가입을 받습니다)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="use_join" id="use_join_n" value="N" <?= ($join_config['use_join'] ?? 'Y') == 'N' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="use_join_n">
                                        차단 (회원가입을 받지 않습니다)
                                    </label>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- 승인 방식 -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">회원 승인 방식</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="approval_type" id="approval_auto" value="auto" <?= ($join_config['approval_type'] ?? 'auto') == 'auto' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="approval_auto">
                                        자동 승인 (가입 즉시 활성화)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="approval_type" id="approval_manual" value="manual" <?= ($join_config['approval_type'] ?? 'auto') == 'manual' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="approval_manual">
                                        관리자 승인 (관리자가 수동으로 승인)
                                    </label>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- 필수 입력 항목 -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">회원가입 필수 항목</label>
                                <p class="text-muted small mb-3">체크된 항목은 회원가입 시 필수로 입력해야 합니다.</p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="required_fields[]" value="user_id" id="req_user_id" checked disabled>
                                            <label class="form-check-label" for="req_user_id">
                                                <i class="fas fa-user text-primary"></i> 아이디 <span class="badge bg-secondary">필수</span>
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="required_fields[]" value="password" id="req_password" checked disabled>
                                            <label class="form-check-label" for="req_password">
                                                <i class="fas fa-lock text-danger"></i> 비밀번호 <span class="badge bg-secondary">필수</span>
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="required_fields[]" value="email" id="req_email" <?= in_array('email', $join_config['required_fields'] ?? ['email']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="req_email">
                                                <i class="fas fa-envelope text-info"></i> 이메일
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="required_fields[]" value="name" id="req_name" <?= in_array('name', $join_config['required_fields'] ?? ['name']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="req_name">
                                                <i class="fas fa-id-card text-success"></i> 이름
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="required_fields[]" value="phone" id="req_phone" <?= in_array('phone', $join_config['required_fields'] ?? []) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="req_phone">
                                                <i class="fas fa-mobile-alt text-warning"></i> 휴대폰번호
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="required_fields[]" value="address" id="req_address" <?= in_array('address', $join_config['required_fields'] ?? []) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="req_address">
                                                <i class="fas fa-map-marker-alt text-danger"></i> 주소
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="required_fields[]" value="tel" id="req_tel" <?= in_array('tel', $join_config['required_fields'] ?? []) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="req_tel">
                                                <i class="fas fa-phone text-primary"></i> 연락처
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" onclick="saveEnv()">
                                    <i class="fas fa-save me-2"></i>저장
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- 이용약관 -->
            <div class="tab-pane fade" id="terms" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-file-contract"></i> 이용약관
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="termsForm">
                            <div class="mb-3">
                                <textarea class="form-control font-monospace" name="terms_of_service" rows="20" placeholder="이용약관을 입력하세요..."><?= xssFilter($terms_of_service ?? '') ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" onclick="saveTerms()">
                                    <i class="fas fa-save me-2"></i>저장
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- 개인정보보호정책 -->
            <div class="tab-pane fade" id="privacy" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shield-alt"></i> 개인정보보호정책
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="privacyForm">
                            <div class="mb-3">
                                <textarea class="form-control font-monospace" name="privacy_policy" rows="20" placeholder="개인정보보호정책을 입력하세요..."><?= xssFilter($privacy_policy ?? '') ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" onclick="savePrivacy()">
                                    <i class="fas fa-save me-2"></i>저장
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- 청소년보호정책 -->
            <div class="tab-pane fade" id="youth" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-child"></i> 청소년보호정책
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="youthForm">
                            <div class="mb-3">
                                <textarea class="form-control font-monospace" name="youth_protection" rows="20" placeholder="청소년보호정책을 입력하세요..."><?= xssFilter($youth_protection ?? '') ?></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" onclick="saveYouth()">
                                    <i class="fas fa-save me-2"></i>저장
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
        
    </main>
</div>

<script>
function saveEnv() {
    const form = document.getElementById('envForm');
    const formData = new FormData(form);
    
    // 체크박스 배열 처리
    const requiredFields = [];
    document.querySelectorAll('input[name="required_fields[]"]:checked').forEach(cb => {
        requiredFields.push(cb.value);
    });
    
    formData.delete('required_fields[]');
    formData.append('required_fields', JSON.stringify(requiredFields));
    formData.append('config_type', 'join_env');
    
    fetch('/admin/joinconfig/save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('가입 환경 설정이 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function saveTerms() {
    const form = document.getElementById('termsForm');
    const formData = new FormData(form);
    formData.append('config_key', 'terms_of_service');
    
    fetch('/admin/config/save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('이용약관이 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function savePrivacy() {
    const form = document.getElementById('privacyForm');
    const formData = new FormData(form);
    formData.append('config_key', 'privacy_policy');
    
    fetch('/admin/config/save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('개인정보보호정책이 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function saveYouth() {
    const form = document.getElementById('youthForm');
    const formData = new FormData(form);
    formData.append('config_key', 'youth_protection');
    
    fetch('/admin/config/save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('청소년보호정책이 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
