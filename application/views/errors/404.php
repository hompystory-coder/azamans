<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - 페이지를 찾을 수 없습니다</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            padding: 50px 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #667eea;
            margin: 0;
            line-height: 1;
            text-shadow: 3px 3px 0 rgba(0, 0, 0, 0.1);
            animation: bounce 1s ease infinite alternate;
        }
        
        @keyframes bounce {
            from {
                transform: translateY(0);
            }
            to {
                transform: translateY(-10px);
            }
        }
        
        .error-icon {
            font-size: 80px;
            color: #764ba2;
            margin: 20px 0;
            animation: swing 2s ease-in-out infinite;
        }
        
        @keyframes swing {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(10deg);
            }
            75% {
                transform: rotate(-10deg);
            }
        }
        
        .error-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 20px 0 10px;
        }
        
        .error-message {
            font-size: 18px;
            color: #666;
            margin: 10px 0 30px;
            line-height: 1.6;
        }
        
        .btn-home {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
            color: white;
        }
        
        .btn-home i {
            margin-right: 10px;
        }
        
        .helpful-links {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }
        
        .helpful-links h4 {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .link-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        
        .link-item {
            padding: 8px 20px;
            background: #f8f9fa;
            border-radius: 20px;
            text-decoration: none;
            color: #667eea;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .link-item:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        
        .link-item i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-compass"></i>
        </div>
        <h1 class="error-code">404</h1>
        <h2 class="error-title">페이지를 찾을 수 없습니다</h2>
        <p class="error-message">
            요청하신 페이지가 존재하지 않거나 이동되었습니다.<br>
            URL을 다시 확인해 주세요.
        </p>
        <a href="/" class="btn-home">
            <i class="fas fa-home"></i>홈으로 돌아가기
        </a>
        
        <div class="helpful-links">
            <h4>도움이 될 만한 링크</h4>
            <div class="link-list">
                <a href="/bbs/free" class="link-item">
                    <i class="fas fa-comments"></i>자유게시판
                </a>
                <a href="/bbs/notice" class="link-item">
                    <i class="fas fa-bullhorn"></i>공지사항
                </a>
                <a href="/bbs/qna" class="link-item">
                    <i class="fas fa-question-circle"></i>Q&A
                </a>
                <a href="/bbs/gallery" class="link-item">
                    <i class="fas fa-images"></i>갤러리
                </a>
            </div>
        </div>
    </div>
</body>
</html>
