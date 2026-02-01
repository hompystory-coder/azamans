<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?= xssFilter($title) ?></h2>
                <p class="text-muted mb-0"><?= date('Y년 m월 d일 H:i') ?></p>
            </div>
        </div>
        
        <!-- 안내 -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>파비콘이란?</strong> 브라우저 탭이나 북마크에 표시되는 작은 아이콘입니다. 권장 크기는 32x32 픽셀 또는 16x16 픽셀입니다.
        </div>
        
        <!-- 파비콘 생성기 -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-magic"></i> 파비콘 생성기
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-upload"></i> 파비콘 업로드
                </h5>
            </div>
            <div class="card-body">
                <form id="faviconForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">파비콘 파일 선택</label>
                        <input type="file" class="form-control" id="faviconFile" name="favicon" accept=".ico,.png,.jpg,.jpeg,.gif">
                        <small class="text-muted">지원 형식: ICO, PNG, JPG, GIF (최대 1MB)</small>
                    </div>
                    
                    <div class="mb-3" id="preview" style="display: none;">
                        <label class="form-label">미리보기</label>
                        <div>
                            <img id="previewImage" src="" style="max-width: 64px; max-height: 64px;">
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-primary" onclick="uploadFavicon()">
                        <i class="fas fa-upload me-2"></i>업로드
                    </button>
                </form>
            </div>
        </div>
        
        <!-- 현재 파비콘 -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-image"></i> 현재 파비콘
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($favicon_url)): ?>
                <div class="d-flex align-items-center">
                    <img src="<?= xssFilter($favicon_url) ?>" style="width: 32px; height: 32px;" class="me-3">
                    <div>
                        <p class="mb-1"><?= xssFilter($favicon_url) ?></p>
                        <button class="btn btn-sm btn-danger" onclick="deleteFavicon()">
                            <i class="fas fa-trash"></i> 삭제
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <p class="text-muted mb-0">현재 설정된 파비콘이 없습니다.</p>
                <?php endif; ?>
            </div>
        </div>
        
    </main>
</div>

<script>
// 파일 선택 시 미리보기
document.getElementById('faviconFile').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

function uploadFavicon() {
    const fileInput = document.getElementById('faviconFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('파일을 선택해주세요.');
        return;
    }
    
    if (file.size > 1024 * 1024) {
        alert('파일 크기는 1MB를 초과할 수 없습니다.');
        return;
    }
    
    const formData = new FormData();
    formData.append('favicon', file);
    
    fetch('/admin/favicon/upload', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('파비콘이 업로드되었습니다.');
            location.reload();
        } else {
            alert(data.message || '업로드에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function deleteFavicon() {
    if (!confirm('파비콘을 삭제하시겠습니까?')) return;
    
    fetch('/admin/favicon/delete', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('파비콘이 삭제되었습니다.');
            location.reload();
        } else {
            alert(data.message || '삭제에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
