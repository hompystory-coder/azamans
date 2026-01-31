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
                    <i class="fas fa-image text-main me-2"></i>
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
                <strong>파비콘이란?</strong> 브라우저 탭이나 북마크에 표시되는 작은 아이콘입니다. 
                권장 크기는 32x32 픽셀 또는 16x16 픽셀입니다.
            </div>
            
            <!-- 파비콘 생성기 링크 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-magic text-main me-2"></i>파비콘 생성기
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-3">아래 사이트에서 이미지를 업로드하여 다양한 크기의 파비콘을 생성할 수 있습니다.</p>
                    <a href="https://www.favicon-generator.org/" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-2"></i>Favicon Generator 바로가기
                    </a>
                </div>
            </div>
            
            <!-- 파비콘 업로드 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-upload text-main me-2"></i>파비콘 업로드
                    </h5>
                </div>
                <div class="card-body">
                    <form id="faviconForm" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label fw-bold">현재 파비콘</label>
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <?php if ($favicon_url): ?>
                                    <img src="<?php echo xssFilter($favicon_url); ?>" 
                                         width="32" height="32" class="me-3 border">
                                    <div>
                                        <div class="fw-bold">등록됨</div>
                                        <small class="text-muted"><?php echo xssFilter($favicon_url); ?></small>
                                    </div>
                                <?php else: ?>
                                    <i class="fas fa-image fa-2x text-muted me-3"></i>
                                    <div class="text-muted">등록된 파비콘이 없습니다.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">파비콘 파일 선택</label>
                            <input type="file" 
                                   class="form-control" 
                                   name="favicon" 
                                   accept="image/x-icon,image/png,image/gif"
                                   id="faviconFile">
                            <small class="text-muted">지원 형식: .ico, .png, .gif (권장 크기: 32x32 또는 16x16)</small>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                <i class="fas fa-redo me-1"></i>초기화
                            </button>
                            <button type="button" class="btn btn-primary" onclick="uploadFavicon()">
                                <i class="fas fa-save me-1"></i>업로드
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- URL 직접 입력 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-link text-main me-2"></i>파비콘 URL 직접 입력
                    </h5>
                </div>
                <div class="card-body">
                    <form id="faviconUrlForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">파비콘 URL</label>
                            <input type="url" 
                                   class="form-control" 
                                   name="favicon_url" 
                                   value="<?php echo xssFilter($favicon_url); ?>"
                                   placeholder="https://example.com/favicon.ico">
                            <small class="text-muted">외부 URL을 직접 입력할 수 있습니다.</small>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="saveFaviconUrl()">
                                <i class="fas fa-save me-1"></i>저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    function uploadFavicon() {
        const form = document.getElementById('faviconForm');
        const formData = new FormData(form);
        
        const fileInput = document.getElementById('faviconFile');
        if (!fileInput.files.length) {
            alert('파일을 선택해주세요.');
            return;
        }
        
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>업로드 중...';
        
        fetch('/admin/site/favicon/upload', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('파비콘이 업로드되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(err => {
            console.error(err);
            alert('업로드 중 오류가 발생했습니다.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
    
    function saveFaviconUrl() {
        const form = document.getElementById('faviconUrlForm');
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
                alert('설정이 저장되었습니다.');
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
