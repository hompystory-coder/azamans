<?php include __DIR__ . '/../_header.php'; ?>

<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm animate__animated animate__fadeIn">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <div class="display-4 text-main mb-3">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <h2 class="fw-bold mb-2">로그인</h2>
                            <p class="text-muted">환영합니다! 로그인 후 다양한 서비스를 이용하세요.</p>
                        </div>
                        
                        <div id="alertBox" class="alert alert-dismissible fade show" role="alert" style="display: none;">
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            <span id="alertMessage"></span>
                        </div>
                        
                        <form id="loginForm" onsubmit="handleLogin(event)">
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user me-1"></i>아이디
                                </label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" 
                                       required placeholder="아이디를 입력하세요" autocomplete="username">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>비밀번호
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" 
                                       required placeholder="비밀번호를 입력하세요" autocomplete="current-password">
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                                <label class="form-check-label" for="rememberMe">
                                    로그인 상태 유지
                                </label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>로그인
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">
                                아직 회원이 아니신가요?
                                <a href="/member/register" class="text-main fw-bold">
                                    <i class="fas fa-user-plus me-1"></i>회원가입
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
function handleLogin(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const alertBox = document.getElementById('alertBox');
    const alertMessage = document.getElementById('alertMessage');
    const submitBtn = e.target.querySelector('button[type="submit"]');
    
    // 버튼 비활성화
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>로그인 중...';
    
    // 로딩 표시
    alertBox.className = 'alert alert-info alert-dismissible fade show';
    alertBox.style.display = 'block';
    alertMessage.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>로그인 중...';
    
    console.log('로그인 시도:', {
        username: formData.get('username'),
        url: '/member/loginProcess'
    });
    
    fetch('/member/loginProcess', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        console.log('Response status:', res.status);
        return res.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            alertBox.className = 'alert alert-success alert-dismissible fade show';
            alertMessage.innerHTML = '<i class="fas fa-check-circle me-2"></i>' + data.message;
            
            setTimeout(() => {
                window.location.href = data.redirect || '/';
            }, 500);
        } else {
            alertBox.className = 'alert alert-danger alert-dismissible fade show';
            alertMessage.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + data.message;
            
            // 버튼 활성화
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>로그인';
        }
    })
    .catch(err => {
        console.error('Login error:', err);
        alertBox.className = 'alert alert-danger alert-dismissible fade show';
        alertMessage.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>로그인 처리 중 오류가 발생했습니다. 콘솔을 확인하세요.';
        
        // 버튼 활성화
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>로그인';
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
