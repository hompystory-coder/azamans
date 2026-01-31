<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - <?php echo env('APP_NAME', 'MVC Framework'); ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <style>
        .register-container {
            max-width: 500px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-header h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .register-header p {
            color: #7f8c8d;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #34495e;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
        }
        .form-help {
            font-size: 0.85rem;
            color: #7f8c8d;
            margin-top: 5px;
        }
        .btn-register {
            width: 100%;
            padding: 14px;
            background: #2ecc71;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-register:hover {
            background: #27ae60;
        }
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
        .form-footer a {
            color: #3498db;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }
        .alert.error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .alert.success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }
        .alert ul {
            margin: 10px 0 0 20px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../_header.php'; ?>
    
    <main>
        <div class="register-container">
            <div class="register-header">
                <h1>회원가입</h1>
                <p>새로운 계정을 만들어 다양한 서비스를 이용하세요.</p>
            </div>
            
            <div id="alertBox" class="alert"></div>
            
            <form id="registerForm" onsubmit="handleRegister(event)">
                <div class="form-group">
                    <label for="username">아이디 <span style="color:red">*</span></label>
                    <input type="text" id="username" name="username" required 
                           placeholder="4자 이상 입력" minlength="4">
                    <p class="form-help">영문, 숫자 조합 4자 이상</p>
                </div>
                
                <div class="form-group">
                    <label for="name">이름 <span style="color:red">*</span></label>
                    <input type="text" id="name" name="name" required 
                           placeholder="이름을 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="email">이메일 <span style="color:red">*</span></label>
                    <input type="email" id="email" name="email" required 
                           placeholder="example@email.com">
                </div>
                
                <div class="form-group">
                    <label for="password">비밀번호 <span style="color:red">*</span></label>
                    <input type="password" id="password" name="password" required 
                           placeholder="8자 이상 입력" minlength="8">
                    <p class="form-help">8자 이상, 영문/숫자/특수문자 조합 권장</p>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">비밀번호 확인 <span style="color:red">*</span></label>
                    <input type="password" id="password_confirm" name="password_confirm" required 
                           placeholder="비밀번호를 다시 입력하세요">
                </div>
                
                <button type="submit" class="btn-register">회원가입</button>
            </form>
            
            <div class="form-footer">
                <p>이미 회원이신가요? <a href="/member/login">로그인</a></p>
                <p><a href="/">홈으로 돌아가기</a></p>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../_footer.php'; ?>
    
    <script>
    function handleRegister(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const alertBox = document.getElementById('alertBox');
        
        // 비밀번호 확인
        const password = formData.get('password');
        const passwordConfirm = formData.get('password_confirm');
        
        if (password !== passwordConfirm) {
            alertBox.className = 'alert error';
            alertBox.style.display = 'block';
            alertBox.textContent = '비밀번호가 일치하지 않습니다.';
            return;
        }
        
        // 로딩 표시
        alertBox.className = 'alert';
        alertBox.style.display = 'block';
        alertBox.textContent = '회원가입 처리 중...';
        
        fetch('/member/registerProcess', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alertBox.className = 'alert success';
                alertBox.textContent = data.message;
                
                setTimeout(() => {
                    window.location.href = data.redirect || '/member/login';
                }, 1000);
            } else {
                alertBox.className = 'alert error';
                
                if (data.errors && Array.isArray(data.errors)) {
                    alertBox.innerHTML = '<strong>오류:</strong><ul>';
                    data.errors.forEach(err => {
                        alertBox.innerHTML += `<li>${err}</li>`;
                    });
                    alertBox.innerHTML += '</ul>';
                } else {
                    alertBox.textContent = data.message || '회원가입 중 오류가 발생했습니다.';
                }
            }
            alertBox.style.display = 'block';
        })
        .catch(err => {
            console.error('Register error:', err);
            alertBox.className = 'alert error';
            alertBox.textContent = '회원가입 처리 중 오류가 발생했습니다.';
            alertBox.style.display = 'block';
        });
    }
    </script>
</body>
</html>
