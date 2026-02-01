<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>phpMyAdmin 설치 완료</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
        }
        .success-icon {
            font-size: 60px;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-access {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-size: 18px;
            margin-top: 20px;
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .db-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="success-icon">✅</div>
        <h2 class="text-center mb-4">phpMyAdmin 설치 완료!</h2>
        
        <div class="info-box">
            <h5>📦 설치 정보</h5>
            <ul>
                <li><strong>버전:</strong> phpMyAdmin 5.2.3</li>
                <li><strong>설치 경로:</strong> /home/mvc/phpmyadmin</li>
                <li><strong>접속 경로:</strong> /phpmyadmin</li>
                <li><strong>언어:</strong> 한국어 (기본)</li>
            </ul>
        </div>

        <div class="db-info">
            <h5>🔐 데이터베이스 접속 정보</h5>
            <ul>
                <li><strong>호스트:</strong> localhost</li>
                <li><strong>사용자:</strong> root</li>
                <li><strong>데이터베이스:</strong> mvc</li>
                <li><strong>문자셋:</strong> utf8mb4</li>
            </ul>
            <p class="mb-0 text-muted small">
                <strong>참고:</strong> 비밀번호는 .env 파일에 저장되어 있습니다.
            </p>
        </div>

        <div class="alert alert-info mt-3">
            <strong>💡 사용 안내:</strong><br>
            • phpMyAdmin에 로그인하려면 데이터베이스 사용자 계정이 필요합니다<br>
            • 로그인 후 mvc 데이터베이스를 선택하여 테이블을 관리할 수 있습니다<br>
            • SQL 탭에서 직접 쿼리를 실행할 수 있습니다
        </div>

        <div class="text-center">
            <a href="/phpmyadmin" class="btn btn-primary btn-access btn-lg w-100">
                🚀 phpMyAdmin 접속하기
            </a>
        </div>

        <div class="text-center mt-3">
            <a href="/" class="btn btn-outline-secondary">
                ← 메인으로 돌아가기
            </a>
        </div>

        <div class="alert alert-warning mt-4 small">
            <strong>⚠️ 보안 주의사항:</strong><br>
            프로덕션 환경에서는 phpMyAdmin 접근을 제한하는 것이 좋습니다.<br>
            IP 제한이나 .htaccess를 통한 인증을 권장합니다.
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
