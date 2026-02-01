<?php include __DIR__ . '/_admin_header.php'; ?>

<style>
/* CKEditor 높이 설정 */
#page_content + .ck-editor .ck-editor__editable {
    min-height: 600px !important;
    max-height: 800px !important;
}
</style>

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
                                        <i class="fas fa-edit me-1"></i>페이지 내용 (HTML 편집)
                                    </label>
                                    <textarea class="form-control" name="page_content" id="page_content"><?php echo $pageContent; ?></textarea>
                                    <small class="text-muted">CKEditor를 통해 내용을 편집하세요.</small>
                                    
                                    <!-- 첨부파일 업로드 -->
                                    <div class="mt-3">
                                        <label class="form-label">
                                            <i class="fas fa-paperclip me-1"></i>첨부파일
                                        </label>
                                        <input type="file" class="form-control" name="page_files[]" id="page_files" multiple>
                                        <small class="text-muted">여러 파일 선택 가능 (최대 10개, 각 10MB)</small>
                                        
                                        <!-- 기존 첨부파일 목록 -->
                                        <?php if (!empty($pageFiles)): ?>
                                        <div class="mt-3">
                                            <strong>기존 첨부파일:</strong>
                                            <ul class="list-group mt-2" id="existing-files-list">
                                                <?php foreach ($pageFiles as $file): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center" id="file-<?php echo $file['uid']; ?>">
                                                    <div>
                                                        <i class="fas fa-file me-2"></i>
                                                        <a href="/page/download/<?php echo $file['uid']; ?>" target="_blank">
                                                            <?php echo xssFilter($file['original_name']); ?>
                                                        </a>
                                                        <small class="text-muted ms-2">(<?php echo number_format($file['filesize'] / 1024, 2); ?> KB, 다운로드: <?php echo $file['download_count']; ?>회)</small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="deletePageFile(<?php echo $file['uid']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </div>
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
        
        // CKEditor refresh (hidden 상태에서 초기화되면 레이아웃 깨짐 방지)
        if (selectedType === 'page' && window.editorpage_content) {
            setTimeout(function() {
                window.editorpage_content.editing.view.change(writer => {
                    writer.setStyle('height', '500px', window.editorpage_content.editing.view.document.getRoot());
                });
            }, 100);
        }
    }
    
    // 초기 로드
    showTypeOptions();
    
    // 메뉴 타입 변경 시
    $('input[name="menu_type"]').on('change', showTypeOptions);
    
    // 폼 제출
    $('#menuEditForm').on('submit', function(e) {
        e.preventDefault();
        
        // CKEditor 내용 동기화
        if (window.editorpage_content) {
            document.getElementById('page_content').value = window.editorpage_content.getData();
        }
        
        const menuId = <?php echo $menu['id']; ?>;
        const formData = new FormData(this);
        
        // 체크박스 값 처리
        formData.set('use_redirect', $('#use_redirect').is(':checked') ? 'Y' : 'N');
        formData.set('is_hidden', $('#is_hidden').is(':checked') ? 'Y' : 'N');
        formData.set('is_blocked', $('#is_blocked').is(':checked') ? 'Y' : 'N');
        
        // 선택된 타입에 따라 menu_target 처리
        const selectedType = $('input[name="menu_type"]:checked').val();
        if (selectedType === 'page') {
            formData.delete('menu_target');
            formData.set('menu_target', '');
            
            // 첨부파일 업로드 (별도 처리)
            const fileInput = document.getElementById('page_files');
            if (fileInput && fileInput.files.length > 0) {
                const filesFormData = new FormData();
                filesFormData.append('menu_id', menuId);
                
                for (let i = 0; i < fileInput.files.length; i++) {
                    filesFormData.append('files[]', fileInput.files[i]);
                }
                
                // 첨부파일 먼저 업로드
                $.ajax({
                    url: '/upload/pageAttach',
                    method: 'POST',
                    data: filesFormData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(fileResponse) {
                        console.log('파일 업로드 완료:', fileResponse);
                        // 파일 업로드 후 메뉴 업데이트
                        updateMenu(formData, menuId);
                    },
                    error: function(xhr) {
                        console.error('파일 업로드 실패:', xhr);
                        alert('첨부파일 업로드 중 오류가 발생했습니다.');
                    }
                });
            } else {
                // 첨부파일 없으면 바로 메뉴 업데이트
                updateMenu(formData, menuId);
            }
        } else if (selectedType === 'board') {
            // 게시판 선택값만 사용
            formData.set('menu_target', $('#board_select').val());
            updateMenu(formData, menuId);
        } else if (selectedType === 'content') {
            // 콘텐츠 선택값만 사용
            formData.set('menu_target', $('#content_select').val());
            updateMenu(formData, menuId);
        }
    });
    
    // 메뉴 업데이트 함수
    function updateMenu(formData, menuId) {
        // page_files[] 제거 (이미 업로드됨)
        formData.delete('page_files[]');
        
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
                    alert(response.message || '메뉴 수정에 실패했습니다.');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                console.error('Response:', xhr.responseText);
                alert('메뉴 수정 중 오류가 발생했습니다.\n오류: ' + (xhr.responseText || xhr.statusText));
            }
        });
    }
});

// 페이지 첨부파일 삭제
function deletePageFile(fileId) {
    if (!confirm('이 파일을 삭제하시겠습니까?')) {
        return;
    }
    
    $.ajax({
        url: '/admin/deletePageFile/' + fileId,
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert(response.message);
                $('#file-' + fileId).fadeOut(300, function() {
                    $(this).remove();
                });
            } else {
                alert(response.message);
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            alert('파일 삭제 중 오류가 발생했습니다.');
        }
    });
}
</script>

<!-- CKEditor 스크립트 로드 -->
<script src="/public/plugins/editor/build/ckeditor.js?v=<?php echo time(); ?>"></script>

<script>
// CKEditor 초기화 (지연 로드)
let editorpage_content = null;

function initPageContentEditor() {
    if (editorpage_content) {
        return; // 이미 초기화됨
    }
    
    if (typeof ClassicEditor === 'undefined') {
        console.error('❌ ClassicEditor가 로드되지 않았습니다.');
        return;
    }
    
    ClassicEditor
        .create(document.querySelector('#page_content'), {
            toolbar: {
                items: [
                    'findAndReplace', '|',
                    'heading', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'link', 'uploadImage', 'insertTable', 'blockQuote', 'mediaEmbed', '|',
                    'alignment', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'code', 'codeBlock', '|',
                    'highlight', 'removeFormat', '|',
                    'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                    'htmlEmbed', 'sourceEditing', '|',
                    'undo', 'redo'
                ],
                shouldNotGroupWhenFull: true
            },
            language: 'ko',
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'toggleImageCaption',
                    '|',
                    'imageStyle:inline',
                    'imageStyle:wrapText',
                    'imageStyle:breakText',
                    '|',
                    'resizeImage',
                    '|',
                    'linkImage'
                ]
            },
            simpleUpload: {
                uploadUrl: '/upload/page/image'
            },
            licenseKey: 'GPL'
        })
        .then(editor => {
            window.editorpage_content = editor;
            console.log('✅ CKEditor 초기화 완료: page_content');
        })
        .catch(error => {
            console.error('❌ CKEditor 초기화 오류:', error);
        });
}

// 페이지 로드 시 메뉴 타입 확인 후 CKEditor 초기화
$(document).ready(function() {
    const currentType = $('input[name="menu_type"]:checked').val();
    if (currentType === 'page') {
        // display:block 상태에서 초기화
        setTimeout(initPageContentEditor, 300);
    }
    
    // 메뉴 타입 변경 시 CKEditor 초기화
    $('input[name="menu_type"]').on('change', function() {
        if ($(this).val() === 'page') {
            setTimeout(initPageContentEditor, 300);
        }
    });
});
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
