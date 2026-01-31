<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title); ?> - <?php echo env('APP_NAME', 'MVC Framework'); ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <style>
        .about-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .about-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }
        .about-header h1 {
            font-size: 2.5rem;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .about-content {
            line-height: 1.8;
            color: #34495e;
        }
        .about-content h2 {
            color: #2c3e50;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        .about-content h3 {
            color: #34495e;
            margin-top: 25px;
            margin-bottom: 12px;
            font-size: 1.4rem;
        }
        .about-content p {
            margin-bottom: 15px;
        }
        .about-content ul {
            margin: 15px 0;
            padding-left: 25px;
        }
        .about-content li {
            margin-bottom: 10px;
        }
        .about-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e1e8ed;
            text-align: center;
        }
        .about-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
        }
        .about-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../_header.php'; ?>
    
    <main>
        <div class="about-container">
            <div class="about-header">
                <h1><?php echo xssFilter($about_title); ?></h1>
            </div>
            
            <div class="about-content">
                <?php echo $content; ?>
            </div>
            
            <div class="about-footer">
                <a href="/">← 홈으로 돌아가기</a>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../_footer.php'; ?>
</body>
</html>
