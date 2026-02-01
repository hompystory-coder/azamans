<!-- 디버그: 세션 정보 -->
<?php if (false): // 디버그 모드 (false = 숨김) ?>
<div class="alert alert-info">
    <strong>디버그 정보:</strong><br>
    로그인 상태: <?php echo isLoggedIn() ? '로그인' : '비로그인'; ?><br>
    관리자: <?php echo isAdmin() ? '예' : '아니오'; ?><br>
    user_id: <?php echo $_SESSION['user_id'] ?? 'not set'; ?><br>
    nickname: <?php echo $_SESSION['nickname'] ?? 'not set'; ?><br>
    name: <?php echo $_SESSION['name'] ?? 'not set'; ?><br>
    level: <?php echo $_SESSION['level'] ?? 'not set'; ?><br>
    is_admin: <?php echo isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'true' : 'false') : 'not set'; ?>
</div>
<?php endif; ?>

<!-- 댓글 영역 -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-comments me-2"></i>댓글 
            <span id="comment-count" class="badge bg-primary"><?php echo count($comments); ?></span>
        </h5>
    </div>
    <div class="card-body">
        <!-- 댓글 목록 -->
        <div id="comments-list" class="mb-4">
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item border-bottom pb-3 mb-3" data-comment-id="<?php echo $comment['uid']; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong><?php echo xssFilter($comment['name']); ?></strong>
                                <small class="text-muted ms-2">
                                    <?php echo date('Y-m-d H:i', strtotime($comment['reg_date'])); ?>
                                </small>
                                <div class="comment-content mt-2">
                                    <p class="mb-0"><?php echo nl2br(xssFilter($comment['content'])); ?></p>
                                </div>
                                <!-- 수정 폼 (숨김) -->
                                <div class="comment-edit-form" style="display: none;">
                                    <textarea class="form-control mb-2" rows="3"><?php echo xssFilter($comment['content']); ?></textarea>
                                    <div class="text-end">
                                        <button onclick="cancelEdit(<?php echo $comment['uid']; ?>)" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-times me-1"></i>취소
                                        </button>
                                        <button onclick="saveEdit(<?php echo $comment['uid']; ?>)" class="btn btn-sm btn-primary">
                                            <i class="fas fa-save me-1"></i>저장
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if (isLoggedIn() && ((isset($_SESSION['user_id']) && $comment['member_uid'] == $_SESSION['user_id']) || isAdmin())): ?>
                                <div class="comment-actions">
                                    <button onclick="editComment(<?php echo $comment['uid']; ?>)" class="btn btn-sm btn-outline-primary me-1" title="수정">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteCommentLocal(<?php echo $comment['uid']; ?>)" class="btn btn-sm btn-outline-danger" title="삭제">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p id="no-comments" class="text-muted text-center py-4">첫 댓글을 작성해보세요!</p>
            <?php endif; ?>
        </div>
        
        <!-- 댓글 작성 폼 -->
        <?php if ($board['comment_level'] <= ($_SESSION['level'] ?? 0)): ?>
            <form id="commentForm">
                <div class="mb-3">
                    <textarea id="comment-content" name="content" class="form-control" rows="3" 
                              placeholder="댓글을 입력하세요" required></textarea>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-comment me-1"></i>댓글 작성
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                댓글 작성은 로그인 후 가능합니다.
                <a href="/member/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="alert-link">로그인</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// 전역 변수
const boardId = '<?php echo $board['bbs_id']; ?>';
const postUid = <?php echo $post['uid']; ?>;

// 댓글 작성
document.getElementById('commentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const content = document.getElementById('comment-content').value.trim();
    
    if (!content) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    const formData = new FormData();
    formData.append('content', content);
    
    fetch(`/bbs/${boardId}/comment/${postUid}`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 댓글 입력창 초기화
            document.getElementById('comment-content').value = '';
            
            // 댓글 목록 새로고침
            loadComments();
            
            // 성공 메시지 (선택)
            // alert(data.message);
        } else {
            alert(data.message || '댓글 작성에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('댓글 작성 중 오류가 발생했습니다.');
    });
});

// 댓글 목록 불러오기
function loadComments() {
    fetch(`/bbs/${boardId}/${postUid}/comments`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCommentsList(data.comments);
                updateCommentCount(data.count);
            }
        })
        .catch(err => {
            console.error('Error loading comments:', err);
        });
}

// 댓글 목록 업데이트
function updateCommentsList(comments) {
    const listContainer = document.getElementById('comments-list');
    
    if (comments.length === 0) {
        listContainer.innerHTML = '<p id="no-comments" class="text-muted text-center py-4">첫 댓글을 작성해보세요!</p>';
        return;
    }
    
    let html = '';
    comments.forEach(comment => {
        const canEdit = comment.can_edit || false;
        
        html += `
            <div class="comment-item border-bottom pb-3 mb-3" data-comment-id="${comment.uid}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong>${escapeHtml(comment.name)}</strong>
                        <small class="text-muted ms-2">${comment.reg_date}</small>
                        <div class="comment-content mt-2">
                            <p class="mb-0">${escapeHtml(comment.content).replace(/\n/g, '<br>')}</p>
                        </div>
                        <div class="comment-edit-form" style="display: none;">
                            <textarea class="form-control mb-2" rows="3">${escapeHtml(comment.content)}</textarea>
                            <div class="text-end">
                                <button onclick="cancelEdit(${comment.uid})" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-times me-1"></i>취소
                                </button>
                                <button onclick="saveEdit(${comment.uid})" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save me-1"></i>저장
                                </button>
                            </div>
                        </div>
                    </div>
                    ${canEdit ? `
                        <div class="comment-actions">
                            <button onclick="editComment(${comment.uid})" class="btn btn-sm btn-outline-primary me-1" title="수정">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteCommentLocal(${comment.uid})" class="btn btn-sm btn-outline-danger" title="삭제">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    });
    
    listContainer.innerHTML = html;
}

// 댓글 개수 업데이트
function updateCommentCount(count) {
    document.getElementById('comment-count').textContent = count;
}

// 댓글 수정 모드
function editComment(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const contentDiv = commentItem.querySelector('.comment-content');
    const editForm = commentItem.querySelector('.comment-edit-form');
    const actions = commentItem.querySelector('.comment-actions');
    
    contentDiv.style.display = 'none';
    editForm.style.display = 'block';
    actions.style.display = 'none';
}

// 수정 취소
function cancelEdit(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const contentDiv = commentItem.querySelector('.comment-content');
    const editForm = commentItem.querySelector('.comment-edit-form');
    const actions = commentItem.querySelector('.comment-actions');
    
    contentDiv.style.display = 'block';
    editForm.style.display = 'none';
    actions.style.display = 'block';
}

// 수정 저장
function saveEdit(commentId) {
    const commentItem = document.querySelector(`[data-comment-id="${commentId}"]`);
    const textarea = commentItem.querySelector('.comment-edit-form textarea');
    const content = textarea.value.trim();
    
    if (!content) {
        alert('댓글 내용을 입력해주세요.');
        return;
    }
    
    const formData = new FormData();
    formData.append('content', content);
    
    fetch(`/bbs/${boardId}/${postUid}/comment/${commentId}/edit`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadComments();
        } else {
            alert(data.message || '댓글 수정에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('댓글 수정 중 오류가 발생했습니다.');
    });
}

// 댓글 삭제
function deleteCommentLocal(commentId) {
    // 확인 대화상자만 표시 (유효성 검사 제거)
    if (!confirm('정말 삭제하시겠습니까?')) {
        return;
    }
    
    // AJAX 요청
    fetch(`/bbs/${boardId}/${postUid}/comment/${commentId}/delete`, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // 페이지 새로고침
            location.reload();
        } else {
            alert(data.message || '댓글 삭제에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('댓글 삭제 중 오류가 발생했습니다.');
    });
}

// HTML 이스케이프
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
