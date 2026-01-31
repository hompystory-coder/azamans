<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>사이트 설정 - 관리자</title>
    
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
        <?php include __DIR__ . '/_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0">
                    <i class="fas fa-cog text-main me-2"></i>
                    사이트 설정
                </h1>
                <div class="text-muted">
                    <i class="far fa-clock me-1"></i>
                    <?php echo date('Y년 m월 d일 H:i'); ?>
                </div>
            </div>
            
            <!-- 설정 폼 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-globe text-main me-2"></i>기본 설정
                    </h5>
                </div>
                <div class="card-body">
                    <form id="configForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">사이트명</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="site_name" 
                                   value="<?php echo xssFilter($configs['site_name'] ?? 'MVC Framework'); ?>"
                                   placeholder="사이트명을 입력하세요">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">사이트 URL</label>
                            <input type="url" 
                                   class="form-control" 
                                   name="site_url" 
                                   value="<?php echo xssFilter($configs['site_url'] ?? ''); ?>"
                                   placeholder="https://example.com">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">관리자 이메일</label>
                            <input type="email" 
                                   class="form-control" 
                                   name="site_email" 
                                   value="<?php echo xssFilter($configs['site_email'] ?? ''); ?>"
                                   placeholder="admin@example.com">
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">소개 페이지 제목</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="about_title" 
                                   value="<?php echo xssFilter($configs['about_title'] ?? 'MVC Framework 소개'); ?>"
                                   placeholder="소개 페이지 제목">
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">소개 페이지 내용</label>
                            <textarea class="form-control" 
                                      name="about_content" 
                                      rows="10"
                                      placeholder="소개 페이지에 표시될 내용을 입력하세요. HTML 태그 사용 가능합니다."><?php echo $configs['about_content'] ?? ''; ?></textarea>
                            <small class="text-muted">HTML 태그를 사용할 수 있습니다.</small>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="location.reload()">
                                <i class="fas fa-redo me-1"></i>초기화
                            </button>
                            <button type="button" class="btn btn-primary" onclick="saveConfig()">
                                <i class="fas fa-save me-1"></i>저장
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 시스템 정보 -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-server text-main me-2"></i>시스템 정보
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-code fa-2x text-primary me-3"></i>
                                <div>
                                    <small class="text-muted">PHP 버전</small>
                                    <h6 class="mb-0 fw-bold"><?php echo phpversion(); ?></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-database fa-2x text-success me-3"></i>
                                <div>
                                    <small class="text-muted">서버 소프트웨어</small>
                                    <h6 class="mb-0 fw-bold"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-upload fa-2x text-warning me-3"></i>
                                <div>
                                    <small class="text-muted">최대 업로드 크기</small>
                                    <h6 class="mb-0 fw-bold"><?php echo ini_get('upload_max_filesize'); ?></h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <i class="fas fa-clock fa-2x text-danger me-3"></i>
                                <div>
                                    <small class="text-muted">타임존</small>
                                    <h6 class="mb-0 fw-bold"><?php echo date_default_timezone_get(); ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    function saveConfig() {
        const form = document.getElementById('configForm');
        const formData = new FormData(form);
        
        // 로딩 표시
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
            alert('설정 저장 중 오류가 발생했습니다.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
    </script>
</body>
</html>
