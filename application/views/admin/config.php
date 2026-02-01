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
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="image-tab" data-bs-toggle="tab" data-bs-target="#image" type="button" role="tab">
                    <i class="fas fa-photo-video me-2"></i>이미지 설정
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="watermark-tab" data-bs-toggle="tab" data-bs-target="#watermark" type="button" role="tab">
                    <i class="fas fa-copyright me-2"></i>워터마크
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
            
            <!-- 이미지 설정 탭 -->
            <div class="tab-pane fade" id="image" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-photo-video"></i> 이미지 업로드 및 썸네일 설정
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="imageSettingsForm">
                            <!-- 이미지 업로드 설정 -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h6 class="mb-3"><i class="fas fa-upload"></i> 업로드 이미지 설정</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">최대 가로폭 (px)</label>
                                    <input type="number" class="form-control" name="image_max_width" 
                                           value="<?= xssFilter($configs['image_max_width'] ?? '900') ?>" 
                                           min="100" max="3000" step="10"
                                           placeholder="900">
                                    <small class="text-muted">업로드된 이미지가 이 가로폭을 초과하면 자동으로 리사이즈됩니다.</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">이미지 품질 (%)</label>
                                    <input type="number" class="form-control" name="image_quality" 
                                           value="<?= xssFilter($configs['image_quality'] ?? '100') ?>" 
                                           min="1" max="100" step="1"
                                           placeholder="100">
                                    <small class="text-muted">1~100 사이 값 (100 = 최고 품질)</small>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <!-- 썸네일 설정 -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h6 class="mb-3"><i class="fas fa-images"></i> 썸네일 크기 설정</h6>
                                </div>
                                
                                <!-- 큰 썸네일 -->
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">큰 썸네일</h6>
                                            <div class="mb-2">
                                                <label class="form-label small">가로 (px)</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="thumb_big_width" 
                                                       value="<?= xssFilter($configs['thumb_big_width'] ?? '800') ?>" 
                                                       min="1" max="5000" step="1">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">세로 (px)</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="thumb_big_height" 
                                                       value="<?= xssFilter($configs['thumb_big_height'] ?? '600') ?>" 
                                                       min="1" max="5000" step="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 중간 썸네일 -->
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">중간 썸네일</h6>
                                            <div class="mb-2">
                                                <label class="form-label small">가로 (px)</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="thumb_middle_width" 
                                                       value="<?= xssFilter($configs['thumb_middle_width'] ?? '400') ?>" 
                                                       min="1" max="5000" step="1">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">세로 (px)</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="thumb_middle_height" 
                                                       value="<?= xssFilter($configs['thumb_middle_height'] ?? '300') ?>" 
                                                       min="1" max="5000" step="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 작은 썸네일 -->
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">작은 썸네일</h6>
                                            <div class="mb-2">
                                                <label class="form-label small">가로 (px)</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="thumb_small_width" 
                                                       value="<?= xssFilter($configs['thumb_small_width'] ?? '200') ?>" 
                                                       min="1" max="5000" step="1">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">세로 (px)</label>
                                                <input type="number" class="form-control form-control-sm" 
                                                       name="thumb_small_height" 
                                                       value="<?= xssFilter($configs['thumb_small_height'] ?? '150') ?>" 
                                                       min="1" max="5000" step="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">썸네일 해상도 (%)</label>
                                    <input type="number" class="form-control" name="thumb_quality" 
                                           value="<?= xssFilter($configs['thumb_quality'] ?? '100') ?>" 
                                           min="1" max="100" step="1"
                                           placeholder="100">
                                    <small class="text-muted">숫자 100%만 허용 (1~100)</small>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="saveImageSettings()">
                                <i class="fas fa-save me-2"></i>저장
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- 워터마크 설정 탭 -->
            <div class="tab-pane fade" id="watermark" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-copyright"></i> 워터마크 설정
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="watermarkSettingsForm">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="watermark_enabled" 
                                           name="watermark_enabled" value="Y"
                                           <?= ($configs['watermark_enabled'] ?? 'N') === 'Y' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="watermark_enabled">
                                        워터마크 사용
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">워터마크 이미지</label>
                                <input type="file" class="form-control" id="watermark_image_file" 
                                       accept="image/png" onchange="handleWatermarkUpload(this)">
                                <small class="text-muted">투명 배경의 PNG 파일만 사용 가능 (최대 2MB)</small>
                                <?php if (!empty($configs['watermark_image'])): ?>
                                <div class="mt-2">
                                    <img src="<?= xssFilter($configs['watermark_image']) ?>" 
                                         style="max-height: 100px; border: 1px solid #dee2e6; padding: 10px; background: #f8f9fa;" 
                                         id="watermark_preview">
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" 
                                            onclick="deleteWatermark()">
                                        <i class="fas fa-trash"></i> 삭제
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">워터마크 위치</label>
                                <select class="form-select" name="watermark_position">
                                    <option value="1" <?= ($configs['watermark_position'] ?? '5') == '1' ? 'selected' : '' ?>>
                                        왼쪽 상단
                                    </option>
                                    <option value="2" <?= ($configs['watermark_position'] ?? '5') == '2' ? 'selected' : '' ?>>
                                        오른쪽 상단
                                    </option>
                                    <option value="3" <?= ($configs['watermark_position'] ?? '5') == '3' ? 'selected' : '' ?>>
                                        중앙
                                    </option>
                                    <option value="4" <?= ($configs['watermark_position'] ?? '5') == '4' ? 'selected' : '' ?>>
                                        왼쪽 하단
                                    </option>
                                    <option value="5" <?= ($configs['watermark_position'] ?? '5') == '5' ? 'selected' : '' ?>>
                                        오른쪽 하단
                                    </option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">워터마크 투명도 (%)</label>
                                <input type="range" class="form-range" id="watermark_opacity" 
                                       name="watermark_opacity" min="0" max="100" step="5"
                                       value="<?= xssFilter($configs['watermark_opacity'] ?? '80') ?>"
                                       oninput="document.getElementById('opacityValue').textContent = this.value + '%'">
                                <div class="text-center mt-2">
                                    <span id="opacityValue"><?= xssFilter($configs['watermark_opacity'] ?? '80') ?>%</span>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="saveWatermarkSettings()">
                                <i class="fas fa-save me-2"></i>저장
                            </button>
                        </form>
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

function saveImageSettings() {
    const form = document.getElementById('imageSettingsForm');
    const formData = new FormData(form);
    
    fetch('/admin/config/saveImageSettings', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('이미지 설정이 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function saveWatermarkSettings() {
    const form = document.getElementById('watermarkSettingsForm');
    const formData = new FormData(form);
    
    // 체크박스 값 처리
    const enabled = document.getElementById('watermark_enabled').checked;
    formData.set('watermark_enabled', enabled ? 'Y' : 'N');
    
    fetch('/admin/config/saveWatermarkSettings', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('워터마크 설정이 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function handleWatermarkUpload(input) {
    const file = input.files[0];
    if (!file) return;
    
    if (file.type !== 'image/png') {
        alert('PNG 파일만 업로드 가능합니다.');
        input.value = '';
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('파일 크기는 2MB를 초과할 수 없습니다.');
        input.value = '';
        return;
    }
    
    const formData = new FormData();
    formData.append('watermark', file);
    
    fetch('/admin/config/uploadWatermark', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('워터마크가 업로드되었습니다.');
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

function deleteWatermark() {
    if (!confirm('정말 워터마크를 삭제하시겠습니까?')) return;
    
    fetch('/admin/config/deleteWatermark', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('워터마크가 삭제되었습니다.');
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
