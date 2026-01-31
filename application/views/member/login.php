<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - <?php echo env('APP_NAME', 'MVC Framework'); ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .login-header p {
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
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-login:hover {
            background: #2980b9;
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
    </style>
</head>
<body>
    <?php include __DIR__ . '/../_header.php'; ?>
    
    <main>
        <div class="login-container">
            <div class="login-header">
                <h1>로그인</h1>
                <p>환영합니다! 로그인 후 다양한 서비스를 이용하세요.</p>
            </div>
            
            <div id="alertBox" class="alert"></div>
            
            <form id="loginForm" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label for="username">아이디</label>
                    <input type="text" id="username" name="username" required 
                           placeholder="아이디를 입력하세요">
                </div>
                
                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="비밀번호를 입력하세요">
                </div>
                
                <button type="submit" class="btn-login">로그인</button>
            </form>
            
            <div class="form-footer">
                <p>아직 회원이 아니신가요? <a href="/member/register">회원가입</a></p>
                <p><a href="/">홈으로 돌아가기</a></p>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../_footer.php'; ?>
    
    <script>
    function handleLogin(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const alertBox = document.getElementById('alertBox');
        
        // 로딩 표시
        alertBox.className = 'alert';
        alertBox.style.display = 'block';
        alertBox.textContent = '로그인 중...';
        
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
            console.log('Response headers:', res.headers);
            return res.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                alertBox.className = 'alert success';
                alertBox.textContent = data.message;
                
                setTimeout(() => {
                    window.location.href = data.redirect || '/';
                }, 500);
            } else {
                alertBox.className = 'alert error';
                alertBox.textContent = data.message;
            }
        })
        .catch(err => {
            console.error('Login error:', err);
            alertBox.className = 'alert error';
            alertBox.textContent = '로그인 처리 중 오류가 발생했습니다. 콘솔을 확인하세요.';
        });
    }
    </script>
</body>
</html>
