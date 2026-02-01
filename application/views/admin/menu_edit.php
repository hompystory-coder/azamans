<?php include __DIR__ . '/_admin_header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-edit me-2"></i>메뉴 수정
                </h2>
                <a href="/admin/menu/header" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>목록으로
                </a>
            </div>
            
            <form id="menuEditForm">
                <div class="row">
                    <!-- 왼쪽: 메뉴 설정 -->
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">메뉴 기본 설정</h5>
                            </div>
                            <div class="card-body">
                                <!-- 메뉴명 -->
                                <div class="mb-3">
                                    <label for="menu_name" class="form-label">메뉴명 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="menu_name" name="menu_name" value="<?php echo xssFilter($menu['menu_name']); ?>" required>
                                </div>
                                
                                <!-- 메뉴 타입 -->
                                <div class="mb-3">
                                    <label class="form-label">메뉴 타입</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="menu_type" id="type_page" value="page" <?php echo $menu['menu_type'] === 'page' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="type_page">
                                            <i class="fas fa-file-alt me-1"></i>페이지
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="menu_type" id="type_board" value="board" <?php echo $menu['menu_type'] === 'board' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="type_board">
                                            <i class="fas fa-list me-1"></i>게시판
                                        </label>
                                        
                                        <input type="radio" class="btn-check" name="menu_type" id="type_content" value="content" <?php echo $menu['menu_type'] === 'content' ? 'checked' : ''; ?>>
                                        <label class="btn btn-outline-primary" for="type_content">
                                            <i class="fas fa-cube me-1"></i>콘텐츠
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- 페이지 타입: 직접 편집 -->
                                <div class="mb-3 type-option" id="option_page" style="display: none;">
                                    <label class="form-label">
                                        <i class="fas fa-edit me-1"></i>페이지 내용 (HTML 직접 편집)
                                    </label>
                                    <textarea class="form-control font-monospace" name="page_content" id="page_content" rows="15" style="font-size: 0.9rem;"><?php echo htmlspecialchars($pageContent); ?></textarea>
                                    <small class="text-muted">HTML 태그 사용 가능합니다.</small>
                                </div>
                                
                                <!-- 게시판 타입: 게시판 선택 -->
                                <div class="mb-3 type-option" id="option_board" style="display: none;">
                                    <label for="board_select" class="form-label">게시판 선택</label>
                                    <select class="form-select" name="menu_target" id="board_select">
                                        <option value="">게시판을 선택하세요</option>
                                        <?php foreach ($boards as $board): ?>
                                            <option value="<?php echo xssFilter($board['board_id']); ?>" <?php echo ($menu['menu_target'] ?? '') === $board['board_id'] ? 'selected' : ''; ?>>
                                                <?php echo xssFilter($board['board_name']); ?> (<?php echo xssFilter($board['board_id']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- 콘텐츠 타입: 콘텐츠 파일 선택 -->
                                <div class="mb-3 type-option" id="option_content" style="display: none;">
                                    <label for="content_select" class="form-label">콘텐츠 선택</label>
                                    <select class="form-select" name="menu_target" id="content_select">
                                        <option value="">콘텐츠를 선택하세요</option>
                                        <option value="game" <?php echo $menu['menu_target'] === 'game' ? 'selected' : ''; ?>>게임 (임시)</option>
                                        <!-- 추가 콘텐츠는 여기에 -->
                                    </select>
                                </div>
                                
                                <hr>
                                
                                <!-- 커스텀 URL -->
                                <div class="mb-3">
                                    <label for="custom_url" class="form-label">커스텀 URL (선택)</label>
                                    <input type="text" class="form-control" id="custom_url" name="custom_url" value="<?php echo xssFilter($menu['custom_url'] ?? ''); ?>" placeholder="예: https://www.example.com">
                                    <small class="text-muted">외부 링크 또는 특정 URL로 연결할 때 사용</small>
                                </div>
                                
                                <div class="form-checkbox mb-3">
                                    <input type="checkbox" name="use_redirect" id="use_redirect" value="Y" <?php echo ($menu['use_redirect'] ?? 'N') === 'Y' ? 'checked' : ''; ?>>
                                    <label for="use_redirect">
                                        <strong>입력된 주소로 리다이렉트</strong> (외부주소 링크시 사용)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 오른쪽: 옵션 설정 -->
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">창 옵션</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-checkbox mb-2">
                                    <input type="radio" name="target_window" id="window_self" value="self" <?php echo ($menu['target_window'] ?? 'self') === 'self' ? 'checked' : ''; ?>>
                                    <label for="window_self">
                                        현재창
                                    </label>
                                </div>
                                <div class="form-checkbox">
                                    <input type="radio" name="target_window" id="window_blank" value="blank" <?php echo ($menu['target_window'] ?? 'self') === 'blank' ? 'checked' : ''; ?>>
                                    <label for="window_blank">
                                        새창
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0">메뉴 상태</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-checkbox mb-2">
                                    <input type="checkbox" name="is_hidden" id="is_hidden" value="Y" <?php echo ($menu['is_hidden'] ?? 'N') === 'Y' ? 'checked' : ''; ?>>
                                    <label for="is_hidden">
                                        <i class="fas fa-eye-slash me-1"></i>숨김 (URL 접근 가능)
                                    </label>
                                </div>
                                <div class="form-checkbox">
                                    <input type="checkbox" name="is_blocked" id="is_blocked" value="Y" <?php echo ($menu['is_blocked'] ?? 'N') === 'Y' ? 'checked' : ''; ?>>
                                    <label for="is_blocked">
                                        <i class="fas fa-ban me-1"></i>차단 (접근 완전 차단)
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>저장
                            </button>
                            <a href="/admin/menu/header" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>취소
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // 메뉴 타입에 따라 옵션 표시
    function showTypeOptions() {
        $('.type-option').hide();
        const selectedType = $('input[name="menu_type"]:checked').val();
        $('#option_' + selectedType).show();
    }
    
    // 초기 로드
    showTypeOptions();
    
    // 메뉴 타입 변경 시
    $('input[name="menu_type"]').on('change', showTypeOptions);
    
    // 폼 제출
    $('#menuEditForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const menuId = <?php echo $menu['id']; ?>;
        
        // 체크박스 값 처리
        formData.set('use_redirect', $('#use_redirect').is(':checked') ? 'Y' : 'N');
        formData.set('is_hidden', $('#is_hidden').is(':checked') ? 'Y' : 'N');
        formData.set('is_blocked', $('#is_blocked').is(':checked') ? 'Y' : 'N');
        
        // 선택되지 않은 타입의 menu_target 제거
        const selectedType = $('input[name="menu_type"]:checked').val();
        if (selectedType === 'page') {
            formData.delete('menu_target');
        }
        
        $.ajax({
            url: '/admin/updateMenu/' + menuId,
            method: 'POST',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.href = '/admin/menu/header';
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('메뉴 수정 중 오류가 발생했습니다.');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
