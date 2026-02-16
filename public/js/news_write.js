/**
 * 게시판 글쓰기/수정 공통 JavaScript
 * - 확인 메시지 표시
 * - 더블클릭 방지
 * - 버튼 비활성화
 */

// 제출 중 플래그
let isSubmitting = false;

/**
 * 게시물 제출 함수 (글쓰기/수정 공통)
 */
function submitPost(e) {
    e.preventDefault();
    
    // 더블클릭 방지
    if (isSubmitting) {
        console.log('이미 처리 중입니다.');
        return false;
    }
    
    // CKEditor 내용 textarea에 동기화
    if (window.editorcontent) {
        document.getElementById('content').value = window.editorcontent.getData();
    }
    
    // 내용 검증 (빈 값 체크)
    const content = document.getElementById('content').value.trim();
    if (!content || content === '') {
        alert('내용을 입력해주세요.');
        if (window.editorcontent) {
            window.editorcontent.focus();
        }
        return false;
    }
    
    // 확인 메시지
    const mode = e.target.dataset.mode || 'write';
    const confirmMessage = mode === 'write' 
        ? '정말로 등록하시겠습니까?' 
        : '정말로 수정하시겠습니까?';
    
    if (!confirm(confirmMessage)) {
        return false;
    }
    
    // 제출 중 플래그 설정
    isSubmitting = true;
    
    // 버튼 비활성화
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = mode === 'write' ? '등록 중...' : '수정 중...';
        submitBtn.style.opacity = '0.6';
        submitBtn.style.cursor = 'not-allowed';
    }
    
    const formData = new FormData(e.target);
    const url = e.target.getAttribute('data-action-url');
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(res => {
        // 디버깅: 응답 텍스트 먼저 확인
        return res.text().then(text => {
            console.log('Server response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                throw new Error('서버 응답을 처리할 수 없습니다: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.href = data.redirect;
        } else {
            alert(data.message || '처리 중 오류가 발생했습니다.');
            
            // 실패 시 버튼 복구
            isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = mode === 'write' ? '등록' : '수정';
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            }
        }
    })
    .catch(err => {
        alert('처리 중 오류가 발생했습니다.');
        console.error(err);
        
        // 오류 시 버튼 복구
        isSubmitting = false;
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = mode === 'write' ? '등록' : '수정';
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    });
    
    return false;
}

/**
 * 파일 크기 포맷
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * 파일 제거
 */
function removeFile(index) {
    const input = document.getElementById('files');
    if (!input) return;
    
    const dt = new DataTransfer();
    const files = Array.from(input.files);
    
    files.forEach((file, i) => {
        if (i !== index) dt.items.add(file);
    });
    
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
}

/**
 * 기존 파일 삭제
 */
function deleteExistingFile(index) {
    if (!confirm("이 파일을 삭제하시겠습니까?")) {
        return;
    }
    
    // 삭제할 파일 인덱스 표시
    const deleteInput = document.getElementById("delete-file-" + index);
    if (deleteInput) {
        deleteInput.value = "1";
    }
    
    // UI에서 숨김
    const fileItem = document.getElementById("existing-file-" + index);
    if (fileItem) {
        fileItem.style.opacity = "0.5";
        fileItem.style.textDecoration = "line-through";
    }
}

/**
 * 파일 미리보기 초기화
 */
function initFilePreview() {
    const filesInput = document.getElementById('files');
    if (!filesInput) return;
    
    filesInput.addEventListener('change', function(e) {
        const preview = document.getElementById('filePreview');
        if (!preview) return;
        
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
}

// DOM 로드 완료 후 초기화
document.addEventListener('DOMContentLoaded', function() {
    initFilePreview();
});
