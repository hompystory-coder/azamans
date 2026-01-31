<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>사이트 설정 - 관리자</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/admin.css">
    <style>
        .config-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .config-section h2 {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        .form-group textarea {
            min-height: 200px;
            font-family: inherit;
            resize: vertical;
        }
        .btn-save {
            background: #2ecc71;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
        }
        .btn-save:hover {
            background: #27ae60;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body class="admin-body">
    
    <!-- 관리자 사이드바 -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2>관리자</h2>
            <p><?php echo $_SESSION['username']; ?></p>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin">📊 대시보드</a>
            <a href="/admin/config" class="active">⚙️ 사이트 설정</a>
            <a href="/admin/members">👥 회원 관리</a>
            <a href="/admin/boards">📝 게시판 관리</a>
            <a href="/admin/statistics">📈 통계</a>
            <a href="/">🏠 사이트로 돌아가기</a>
            <a href="/member/logout">🚪 로그아웃</a>
        </nav>
    </aside>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1>사이트 설정</h1>
        </div>
        
        <div id="alertBox" class="alert"></div>
        
        <form id="configForm" onsubmit="saveConfig(event)">
            <!-- 기본 설정 -->
            <div class="config-section">
                <h2>기본 설정</h2>
                
                <div class="form-group">
                    <label for="site_name">사이트 이름</label>
                    <input type="text" id="site_name" name="site_name" 
                           value="<?php echo xssFilter(getConfig('site_name', 'MVC Framework')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="site_url">사이트 URL</label>
                    <input type="url" id="site_url" name="site_url" 
                           value="<?php echo xssFilter(getConfig('site_url', 'https://mvc.neuralgrid.kr')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="site_email">관리자 이메일</label>
                    <input type="email" id="site_email" name="site_email" 
                           value="<?php echo xssFilter(getConfig('site_email', 'admin@mvc.local')); ?>">
                </div>
            </div>
            
            <!-- 소개 페이지 설정 -->
            <div class="config-section">
                <h2>소개 페이지</h2>
                
                <div class="form-group">
                    <label for="about_title">소개 페이지 제목</label>
                    <input type="text" id="about_title" name="about_title" 
                           value="<?php echo xssFilter(getConfig('about_title', '사이트 소개')); ?>">
                </div>
                
                <div class="form-group">
                    <label for="about_content">소개 페이지 내용 (HTML 사용 가능)</label>
                    <textarea id="about_content" name="about_content"><?php echo getConfig('about_content', ''); ?></textarea>
                    <p style="font-size: 0.9rem; color: #7f8c8d; margin-top: 5px;">
                        HTML 태그를 사용할 수 있습니다. (예: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt; 등)
                    </p>
                </div>
            </div>
            
            <button type="submit" class="btn-save">💾 저장</button>
        </form>
    </main>
    
    <script>
    function saveConfig(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const alertBox = document.getElementById('alertBox');
        
        alertBox.style.display = 'block';
        alertBox.className = 'alert';
        alertBox.textContent = '저장 중...';
        
        fetch('/admin/configSave', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alertBox.className = 'alert success';
                alertBox.textContent = data.message;
            } else {
                alertBox.className = 'alert error';
                alertBox.textContent = data.message || '저장 중 오류가 발생했습니다.';
            }
        })
        .catch(err => {
            console.error('Save error:', err);
            alertBox.className = 'alert error';
            alertBox.textContent = '저장 중 오류가 발생했습니다.';
        });
    }
    </script>
</body>
</html>
