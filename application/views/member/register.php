<?php include __DIR__ . '/../_header.php'; ?>

<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm animate__animated animate__fadeIn">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="display-4 text-main mb-3">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h2 class="fw-bold mb-2">회원가입</h2>
                            <p class="text-muted">새로운 계정을 만들어보세요.</p>
                        </div>
                        
                        <div id="alertBox" class="alert alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <span id="alertMessage"></span>
                        </div>
                        
                        <form id="registerForm" onsubmit="handleRegister(event)">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-1"></i>아이디 *
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required pattern="[a-zA-Z0-9_]{4,20}" 
                                       placeholder="영문, 숫자, _ 사용 가능 (4-20자)"
                                       autocomplete="username">
                                <div class="form-text">4자 이상 20자 이하, 영문/숫자/_만 사용 가능</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-id-card me-1"></i>이름 *
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       required placeholder="실명을 입력하세요">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-1"></i>이메일 *
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required placeholder="example@email.com"
                                       autocomplete="email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>비밀번호 *
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="8" placeholder="8자 이상 입력하세요"
                                       autocomplete="new-password">
                                <div class="form-text">최소 8자 이상 입력하세요.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">
                                    <i class="fas fa-check-circle me-1"></i>비밀번호 확인 *
                                </label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                                       required minlength="8" placeholder="비밀번호를 다시 입력하세요"
                                       autocomplete="new-password">
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    <a href="#" class="text-main">이용약관</a> 및 
                                    <a href="#" class="text-main">개인정보처리방침</a>에 동의합니다.
                                </label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-user-plus me-2"></i>회원가입
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">
                                이미 계정이 있으신가요?
                                <a href="/member/login" class="text-main fw-bold">
                                    <i class="fas fa-sign-in-alt me-1"></i>로그인
                                </a>
                            </p>
                            <p class="mb-0">
                                <a href="/" class="text-muted">
                                    <i class="fas fa-home me-1"></i>홈으로 돌아가기
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function handleRegister(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const alertBox = document.getElementById('alertBox');
    const alertMessage = document.getElementById('alertMessage');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // 비밀번호 확인
    const password = formData.get('password');
    const passwordConfirm = formData.get('password_confirm');
    
    if (password !== passwordConfirm) {
        alertBox.className = 'alert alert-danger alert-dismissible fade show';
        alertBox.style.display = 'block';
        alertMessage.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>비밀번호가 일치하지 않습니다.';
        return;
    }
    
    // 버튼 비활성화
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>처리 중...';
    
    // 로딩 표시
    alertBox.className = 'alert alert-info alert-dismissible fade show';
    alertBox.style.display = 'block';
    alertMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>회원가입 처리 중...';
    
    fetch('/member/registerProcess', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.className = 'alert alert-success alert-dismissible fade show';
            alertMessage.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
            
            setTimeout(() => {
                window.location.href = data.redirect || '/member/login';
            }, 1000);
        } else {
            alertBox.className = 'alert alert-danger alert-dismissible fade show';
            alertMessage.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + data.message;
            
            // 버튼 활성화
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>회원가입';
        }
    })
    .catch(err => {
        console.error('Register error:', err);
        alertBox.className = 'alert alert-danger alert-dismissible fade show';
        alertMessage.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>회원가입 처리 중 오류가 발생했습니다.';
        
        // 버튼 활성화
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>회원가입';
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
