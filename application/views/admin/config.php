<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">사이트 설정</h2>
                <p class="text-muted mb-0"><?= date('Y년 m월 d일 H:i') ?></p>
            </div>
        </div>
        
        <!-- 탭 메뉴 -->
        <ul class="nav nav-tabs mb-4" id="configTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                    <i class="fas fa-info-circle me-2"></i>기본 정보
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="logo-tab" data-bs-toggle="tab" data-bs-target="#logo" type="button" role="tab">
                    <i class="fas fa-image me-2"></i>로고 설정
                </button>
            </li>
        </ul>
        
        <!-- 탭 컨텐츠 -->
        <div class="tab-content" id="configTabContent">
            
            <!-- 기본 정보 탭 -->
            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> 기본 정보
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="basicInfoForm">
                            <div class="mb-3">
                                <label class="form-label">사이트명</label>
                                <input type="text" class="form-control" name="site_name" 
                                       value="<?= xssFilter($configs['site_name'] ?? 'MVC Framework') ?>" 
                                       placeholder="사이트명을 입력하세요">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">사이트 URL</label>
                                <input type="url" class="form-control" name="site_url" 
                                       value="<?= xssFilter($configs['site_url'] ?? '') ?>" 
                                       placeholder="https://example.com">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">관리자 이메일</label>
                                <input type="email" class="form-control" name="site_email" 
                                       value="<?= xssFilter($configs['site_email'] ?? '') ?>" 
                                       placeholder="admin@example.com">
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="saveBasicInfo()">
                                <i class="fas fa-save me-2"></i>저장
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- 로고 설정 탭 -->
            <div class="tab-pane fade" id="logo" role="tabpanel">
                
                <!-- 헤더 로고 (PC) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-desktop me-2"></i>헤더 로고 (PC)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control mb-2" id="header_logo" accept="image/*" onchange="handleLogoUpload(this, 'header_logo')">
                                <small class="text-muted">권장: PNG, JPG (배경 투명 PNG 권장, 최대 5MB)</small>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">가로 (px)</label>
                                        <input type="number" class="form-control" id="header_logo_width" 
                                               value="<?= $configs['header_logo_width'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('header_logo', 'width')" 
                                               placeholder="자동">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">세로 (px)</label>
                                        <input type="number" class="form-control" id="header_logo_height" 
                                               value="<?= $configs['header_logo_height'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('header_logo', 'height')" 
                                               placeholder="자동">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($configs['header_logo'])): ?>
                        <div class="mt-3">
                            <img src="<?= xssFilter($configs['header_logo']) ?>" style="max-height: 100px; border: 1px solid #dee2e6; padding: 10px;" id="header_logo_preview">
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteLogo('header_logo')">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 푸터 로고 (PC) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-desktop me-2"></i>푸터 로고 (PC)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control mb-2" id="footer_logo" accept="image/*" onchange="handleLogoUpload(this, 'footer_logo')">
                                <small class="text-muted">권장: PNG, JPG (배경 투명 PNG 권장, 최대 5MB)</small>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">가로 (px)</label>
                                        <input type="number" class="form-control" id="footer_logo_width" 
                                               value="<?= $configs['footer_logo_width'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('footer_logo', 'width')" 
                                               placeholder="자동">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">세로 (px)</label>
                                        <input type="number" class="form-control" id="footer_logo_height" 
                                               value="<?= $configs['footer_logo_height'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('footer_logo', 'height')" 
                                               placeholder="자동">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($configs['footer_logo'])): ?>
                        <div class="mt-3">
                            <img src="<?= xssFilter($configs['footer_logo']) ?>" style="max-height: 100px; border: 1px solid #dee2e6; padding: 10px;" id="footer_logo_preview">
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteLogo('footer_logo')">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 모바일 헤더 로고 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-mobile-alt me-2"></i>모바일 헤더 로고
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control mb-2" id="mobile_header_logo" accept="image/*" onchange="handleLogoUpload(this, 'mobile_header_logo')">
                                <small class="text-muted">권장: PNG, JPG (작은 크기 권장, 최대 5MB)</small>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">가로 (px)</label>
                                        <input type="number" class="form-control" id="mobile_header_logo_width" 
                                               value="<?= $configs['mobile_header_logo_width'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('mobile_header_logo', 'width')" 
                                               placeholder="자동">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">세로 (px)</label>
                                        <input type="number" class="form-control" id="mobile_header_logo_height" 
                                               value="<?= $configs['mobile_header_logo_height'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('mobile_header_logo', 'height')" 
                                               placeholder="자동">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($configs['mobile_header_logo'])): ?>
                        <div class="mt-3">
                            <img src="<?= xssFilter($configs['mobile_header_logo']) ?>" style="max-height: 100px; border: 1px solid #dee2e6; padding: 10px;" id="mobile_header_logo_preview">
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteLogo('mobile_header_logo')">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 모바일 푸터 로고 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-mobile-alt me-2"></i>모바일 푸터 로고
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <input type="file" class="form-control mb-2" id="mobile_footer_logo" accept="image/*" onchange="handleLogoUpload(this, 'mobile_footer_logo')">
                                <small class="text-muted">권장: PNG, JPG (작은 크기 권장, 최대 5MB)</small>
                            </div>
                            <div class="col-md-6">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">가로 (px)</label>
                                        <input type="number" class="form-control" id="mobile_footer_logo_width" 
                                               value="<?= $configs['mobile_footer_logo_width'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('mobile_footer_logo', 'width')" 
                                               placeholder="자동">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">세로 (px)</label>
                                        <input type="number" class="form-control" id="mobile_footer_logo_height" 
                                               value="<?= $configs['mobile_footer_logo_height'] ?? '' ?>" 
                                               min="0" step="1"
                                               oninput="handleDimensionChange('mobile_footer_logo', 'height')" 
                                               placeholder="자동">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($configs['mobile_footer_logo'])): ?>
                        <div class="mt-3">
                            <img src="<?= xssFilter($configs['mobile_footer_logo']) ?>" style="max-height: 100px; border: 1px solid #dee2e6; padding: 10px;" id="mobile_footer_logo_preview">
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="deleteLogo('mobile_footer_logo')">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            
        </div>
        
    </main>
</div>

<script>
// 로고별 원본 비율 저장
const logoRatios = {};
let isUpdating = false; // 무한 루프 방지

// 페이지 로드 시 기존 로고의 비율 계산
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach (['header_logo', 'footer_logo', 'mobile_header_logo', 'mobile_footer_logo'] as $logo): ?>
    <?php if (!empty($configs[$logo . '_width']) && !empty($configs[$logo . '_height'])): ?>
    const <?= $logo ?>_width = <?= $configs[$logo . '_width'] ?>;
    const <?= $logo ?>_height = <?= $configs[$logo . '_height'] ?>;
    if (<?= $logo ?>_width > 0 && <?= $logo ?>_height > 0) {
        logoRatios['<?= $logo ?>'] = <?= $logo ?>_width / <?= $logo ?>_height;
    }
    <?php endif; ?>
    <?php endforeach; ?>
});

function handleLogoUpload(input, logoType) {
    const file = input.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        alert('이미지 파일만 업로드 가능합니다.');
        input.value = '';
        return;
    }
    
    if (file.size > 5 * 1024 * 1024) {
        alert('파일 크기는 5MB를 초과할 수 없습니다.');
        input.value = '';
        return;
    }
    
    // 이미지 로드하여 크기 확인
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            // 원본 비율 저장
            logoRatios[logoType] = img.width / img.height;
            
            // 크기 필드에 자동 입력
            document.getElementById(logoType + '_width').value = img.width;
            document.getElementById(logoType + '_height').value = img.height;
            
            // 업로드 실행
            uploadLogo(file, logoType, img.width, img.height);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function uploadLogo(file, logoType, width, height) {
    const formData = new FormData();
    formData.append('logo', file);
    formData.append('logo_type', logoType);
    formData.append('width', width);
    formData.append('height', height);
    
    fetch('/admin/config/uploadLogo', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('로고가 업로드되었습니다.');
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

function handleDimensionChange(logoType, changeType) {
    if (isUpdating) return;
    
    const widthInput = document.getElementById(logoType + '_width');
    const heightInput = document.getElementById(logoType + '_height');
    
    let width = parseInt(widthInput.value) || 0;
    let height = parseInt(heightInput.value) || 0;
    
    // 음수 방지
    if (width < 0) {
        widthInput.value = 0;
        width = 0;
    }
    if (height < 0) {
        heightInput.value = 0;
        height = 0;
    }
    
    const ratio = logoRatios[logoType];
    if (!ratio || ratio <= 0) return;
    
    isUpdating = true;
    
    if (changeType === 'width' && width > 0) {
        const newHeight = Math.round(width / ratio);
        heightInput.value = newHeight;
        saveDimensions(logoType, width, newHeight);
    } else if (changeType === 'height' && height > 0) {
        const newWidth = Math.round(height * ratio);
        widthInput.value = newWidth;
        saveDimensions(logoType, newWidth, height);
    }
    
    setTimeout(() => {
        isUpdating = false;
    }, 100);
}

function saveDimensions(logoType, width, height) {
    // 음수 방지
    if (width < 0 || height < 0) return;
    
    fetch('/admin/config/saveDimensions', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            logo_type: logoType,
            width: width,
            height: height
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log('크기가 저장되었습니다.');
        }
    })
    .catch(err => {
        console.error('크기 저장 오류:', err);
    });
}

function deleteLogo(logoType) {
    if (!confirm('정말 이 로고를 삭제하시겠습니까?')) return;
    
    fetch('/admin/config/deleteLogo', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({logo_type: logoType})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('로고가 삭제되었습니다.');
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

function saveBasicInfo() {
    const form = document.getElementById('basicInfoForm');
    const formData = new FormData(form);
    
    fetch('/admin/config/saveBasic', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('기본 정보가 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
