<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $mode === 'write' ? '글쓰기' : '글수정'; ?> - <?php echo xssFilter($board['bbs_name']); ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/board.css">
    <link rel="stylesheet" href="/public/css/bbs_common.css">
    <style>
        .existing-files {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }
        .existing-files .file-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .existing-files .file-item img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }
        .existing-files .file-item span {
            flex: 1;
        }
        .btn-delete-file {
            padding: 5px 10px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-delete-file:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../../_header.php'; ?>
    
    <main class="container board-container">
        <div class="board-header">
            <h1><?php echo $mode === 'write' ? '글쓰기' : '글수정'; ?></h1>
            <p class="board-desc"><?php echo xssFilter($board['bbs_name']); ?></p>
        </div>
        
        <form id="writeForm" class="write-form" onsubmit="submitPost(event)">
            <?php if (!empty($board['bbs_category']) && !empty($board['bbs_category'])): ?>
            <div class="form-group">
                <label for="category">카테고리</label>
                <select name="category" id="category">
                    <option value="">선택하세요</option>
                    <?php 
                    $categories = json_decode($board['bbs_category'], true);
                    if (is_array($categories)):
                        foreach ($categories as $cat):
                    ?>
                        <option value="<?php echo xssFilter($cat); ?>" 
                            <?php echo (isset($post) && $post['category'] === $cat) ? 'selected' : ''; ?>>
                            <?php echo xssFilter($cat); ?>
                        </option>
                    <?php 
                        endforeach;
                    endif;
                    ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="subject">제목 <span class="required">*</span></label>
                <input type="text" name="subject" id="subject" 
                       value="<?php echo isset($post) ? xssFilter($post['title']) : ''; ?>" 
                       placeholder="제목을 입력하세요" required>
            </div>
            
            <div class="form-group">
                <label for="content">내용 <span class="required">*</span></label>
                <textarea name="content" id="content" placeholder="내용을 입력하세요" required><?php echo isset($post) ? xssFilter($post['content']) : ''; ?></textarea>
                <div class="editor-toolbar">
                    <button type="button" onclick="insertText('**굵게**')" title="굵게">
                        <strong>B</strong>
                    </button>
                    <button type="button" onclick="insertText('_기울임_')" title="기울임">
                        <em>I</em>
                    </button>
                    <button type="button" onclick="insertText('\n---\n')" title="구분선">
                        ─
                    </button>
                    <button type="button" onclick="insertText('\n- 목록\n')" title="목록">
                        ≡
                    </button>
                </div>
            </div>
            
            <?php if ($board['use_upload'] === 'Y'): ?>
            <div class="form-group">
                <label for="files">파일 첨부</label>
                <input type="file" name="files[]" id="files" multiple accept="image/*,.pdf,.zip,.doc,.docx">
                <p class="form-help">최대 10개, 각 파일 최대 10MB</p>
                <div id="filePreview" class="file-preview"></div>
            </div>
            <?php endif; ?>
            
            <?php if (!isLoggedIn()): ?>
            <div class="form-group">
                <label for="writer">작성자 <span class="required">*</span></label>
                <input type="text" name="writer" id="writer" placeholder="작성자명" required>
            </div>
            
            <div class="form-group">
                <label for="password">비밀번호 <span class="required">*</span></label>
                <input type="password" name="password" id="password" placeholder="비밀번호" required>
                <p class="form-help">게시물 수정/삭제 시 필요합니다</p>
            </div>
            <?php endif; ?>
            
            <div class="form-group form-checkbox is-secret">
                <input type="checkbox" name="use_secret" value="Y" id="use_secret"
                    <?php echo (isset($post) && $post['is_secret'] === 'Y') ? 'checked' : ''; ?>>
                <label for="use_secret">비밀글로 작성</label>
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="form-group form-checkbox is-notice">
                <input type="checkbox" name="is_notice" value="Y" id="is_notice"
                    <?php echo (isset($post) && $post['is_notice'] === 'Y') ? 'checked' : ''; ?>>
                <label for="is_notice">공지사항으로 등록</label>
            </div>
            <?php endif; ?>
            
            <div class="write-form-footer">
                <a href="/bbs/<?php echo $board['bbs_id']; ?>" class="btn">취소</a>
                <button type="submit" class="btn btn-primary">
                    <?php echo $mode === 'write' ? '등록' : '수정'; ?>
                </button>
            </div>
        </form>
    </main>
    
    <?php include __DIR__ . '/../../_footer.php'; ?>
    
    <?php 
    // CKEditor 로드
    require_once __DIR__ . '/../../../../editor.php';
    initCKEditor('content', [
        'imageUploadUrl' => '/upload/bbs/image',
        'height' => 500
    ]);
    ?>
    
    <script>
    // 파일 미리보기
    document.getElementById('files')?.addEventListener('change', function(e) {
        const preview = document.getElementById('filePreview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'file-item';
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    item.innerHTML = `
                        <img src="${e.target.result}" alt="${file.name}">
                        <span>${file.name} (${formatFileSize(file.size)})</span>
                        <button type="button" onclick="removeFile(${index})">×</button>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                item.innerHTML = `
                    <span>📎 ${file.name} (${formatFileSize(file.size)})</span>
                    <button type="button" onclick="removeFile(${index})">×</button>
                `;
            }
            
            preview.appendChild(item);
        });
    });
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    function removeFile(index) {
        const input = document.getElementById('files');
        const dt = new DataTransfer();
        const files = Array.from(input.files);
        
        files.forEach((file, i) => {
            if (i !== index) dt.items.add(file);
        });
        
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }
    
    // 기존 파일 삭제
    function deleteExistingFile(index) {
        if (!confirm("이 파일을 삭제하시겠습니까?")) {
            return;
        }
        
        // 삭제할 파일 인덱스 표시
        document.getElementById("delete-file-" + index).value = "1";
        
        // UI에서 숨김
        const fileItem = document.getElementById("existing-file-" + index);
        if (fileItem) {
            fileItem.style.opacity = "0.5";
            fileItem.style.textDecoration = "line-through";
        }
    }
    
    // 텍스트 삽입
    function insertText(text) {
        const textarea = document.getElementById('content');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const before = textarea.value.substring(0, start);
        const after = textarea.value.substring(end);
        
        textarea.value = before + text + after;
        textarea.focus();
        textarea.setSelectionRange(start + text.length, start + text.length);
    }
    
    // 폼 제출
    function submitPost(e) {
        e.preventDefault();
        
        // CKEditor 내용 textarea에 동기화
        if (window.editorcontent) {
            document.getElementById('content').value = window.editorcontent.getData();
        }
        
        const formData = new FormData(e.target);
        const url = <?php echo $mode === 'write' 
            ? "'/bbs/{$board['bbs_id']}/write-process'" 
            : "'/bbs/{$board['bbs_id']}/edit-process/{$post['uid']}'"; ?>;
        
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.href = data.redirect;
            }
        })
        .catch(err => {
            alert('처리 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    </script>
</body>
</html>
