<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title); ?> - 관리자</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard-dynamic-subset.css" />
    <link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0">
                    <i class="fas fa-code text-main me-2"></i>
                    <?php echo xssFilter($title); ?>
                </h1>
                <div class="text-muted">
                    <i class="far fa-clock me-1"></i>
                    <?php echo date('Y년 m월 d일 H:i'); ?>
                </div>
            </div>
            
            <!-- 안내 카드 -->
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>헤더 코드란?</strong> 모든 페이지의 &lt;head&gt; 태그 안에 삽입될 HTML/JavaScript 코드입니다.
                Google Analytics, 메타 태그, 외부 스크립트 등을 추가할 수 있습니다.
            </div>
            
            <!-- 헤더 코드 설정 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-file-code text-main me-2"></i>헤더 코드 입력
                    </h5>
                </div>
                <div class="card-body">
                    <form id="headerCodeForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">HTML/JavaScript 코드</label>
                            <textarea class="form-control font-monospace" 
                                      name="header_code" 
                                      rows="15"
                                      placeholder="<!-- Google Analytics -->
<script async src=&quot;https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID&quot;></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
</script>"><?php echo htmlspecialchars($header_code); ?></textarea>
                            <small class="text-muted">
                                이 코드는 모든 페이지의 &lt;/head&gt; 태그 직전에 삽입됩니다.
                            </small>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                <i class="fas fa-redo me-1"></i>초기화
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveCode()">
                                <i class="fas fa-save me-1"></i>저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 사용 예시 -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-lightbulb text-main me-2"></i>사용 예시
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="examplesAccordion">
                        <!-- Google Analytics -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ga">
                                    Google Analytics
                                </button>
                            </h2>
                            <div id="ga" class="accordion-collapse collapse" data-bs-parent="#examplesAccordion">
                                <div class="accordion-body">
                                    <pre class="bg-light p-3 rounded"><code>&lt;!-- Global site tag (gtag.js) - Google Analytics --&gt;
&lt;script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"&gt;&lt;/script&gt;
&lt;script&gt;
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'GA_MEASUREMENT_ID');
&lt;/script&gt;</code></pre>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Meta Tags -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#meta">
                                    메타 태그 (SEO)
                                </button>
                            </h2>
                            <div id="meta" class="accordion-collapse collapse" data-bs-parent="#examplesAccordion">
                                <div class="accordion-body">
                                    <pre class="bg-light p-3 rounded"><code>&lt;meta name="description" content="사이트 설명"&gt;
&lt;meta name="keywords" content="키워드1, 키워드2, 키워드3"&gt;
&lt;meta property="og:title" content="사이트 제목"&gt;
&lt;meta property="og:description" content="사이트 설명"&gt;
&lt;meta property="og:image" content="이미지 URL"&gt;</code></pre>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Naver Search Advisor -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#naver">
                                    네이버 서치어드바이저
                                </button>
                            </h2>
                            <div id="naver" class="accordion-collapse collapse" data-bs-parent="#examplesAccordion">
                                <div class="accordion-body">
                                    <pre class="bg-light p-3 rounded"><code>&lt;meta name="naver-site-verification" content="인증코드" /&gt;</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    function saveCode() {
        const form = document.getElementById('headerCodeForm');
        const formData = new FormData(form);
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>저장 중...';
        
        fetch('/admin/config/save', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('헤더 코드가 저장되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(err => {
            console.error(err);
            alert('저장 중 오류가 발생했습니다.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
    </script>
</body>
</html>
