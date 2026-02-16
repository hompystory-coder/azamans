<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?php echo xssFilter($title); ?></h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/admin">대시보드</a></li>
                        <li class="breadcrumb-item"><a href="/admin/boards">게시판 관리</a></li>
                        <li class="breadcrumb-item active">게시판 설정</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <?php if (!empty($board)): ?>
        <div class="card">
            <div class="card-body">
                <form id="boardForm" method="POST" action="/admin/board/<?php echo $board['uid']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <h5 class="card-title mb-4">기본 정보</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">UID</label>
                        <input type="text" class="form-control" value="<?php echo $board['uid']; ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">게시판 ID</label>
                        <input type="text" class="form-control" value="<?php echo xssFilter($board['board_id']); ?>" readonly>
                        <small class="form-text text-muted">게시판 주소: /bbs/<?php echo $board['board_id']; ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">게시판 이름 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="board_name" value="<?php echo xssFilter($board['board_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">스킨</label>
                        <select name="board_skin" class="form-select">
                            <option value="default" <?php echo ($board['board_skin'] ?? 'default') === 'default' ? 'selected' : ''; ?>>기본형</option>
                            <option value="gallery" <?php echo ($board['board_skin'] ?? 'default') === 'gallery' ? 'selected' : ''; ?>>갤러리형</option>
                            <option value="blog" <?php echo ($board['board_skin'] ?? 'default') === 'blog' ? 'selected' : ''; ?>>블로그형</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">페이지당 게시물 수</label>
                        <input type="number" class="form-control" name="posts_per_page" value="<?php echo $board['posts_per_page'] ?? 20; ?>" min="10" max="100">
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="card-title mb-4">권한 설정</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">읽기 권한</label>
                        <select name="read_level" class="form-select">
                            <option value="1" <?php echo ($board['read_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="2" <?php echo ($board['read_level'] ?? 1) == 2 ? 'selected' : ''; ?>>정회원 (2)</option>
                            <option value="5" <?php echo ($board['read_level'] ?? 1) == 5 ? 'selected' : ''; ?>>VIP (5)</option>
                            <option value="10" <?php echo ($board['read_level'] ?? 1) == 10 ? 'selected' : ''; ?>>관리자 (10)</option>
                        </select>
                        <small class="form-text text-muted">이 등급 이상만 게시판을 볼 수 있습니다.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">쓰기 권한</label>
                        <select name="write_level" class="form-select">
                            <option value="1" <?php echo ($board['write_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="2" <?php echo ($board['write_level'] ?? 1) == 2 ? 'selected' : ''; ?>>정회원 (2)</option>
                            <option value="5" <?php echo ($board['write_level'] ?? 1) == 5 ? 'selected' : ''; ?>>VIP (5)</option>
                            <option value="10" <?php echo ($board['write_level'] ?? 1) == 10 ? 'selected' : ''; ?>>관리자 (10)</option>
                        </select>
                        <small class="form-text text-muted">이 등급 이상만 글을 작성할 수 있습니다.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">댓글 권한</label>
                        <select name="comment_level" class="form-select">
                            <option value="1" <?php echo ($board['comment_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="2" <?php echo ($board['comment_level'] ?? 1) == 2 ? 'selected' : ''; ?>>정회원 (2)</option>
                            <option value="5" <?php echo ($board['comment_level'] ?? 1) == 5 ? 'selected' : ''; ?>>VIP (5)</option>
                            <option value="10" <?php echo ($board['comment_level'] ?? 1) == 10 ? 'selected' : ''; ?>>관리자 (10)</option>
                        </select>
                        <small class="form-text text-muted">이 등급 이상만 댓글을 작성할 수 있습니다.</small>
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="card-title mb-4">기능 설정</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">댓글 사용</label>
                        <select name="use_comment" class="form-select">
                            <option value="Y" <?php echo ($board['use_comment'] ?? 'Y') === 'Y' ? 'selected' : ''; ?>>사용</option>
                            <option value="N" <?php echo ($board['use_comment'] ?? 'Y') === 'N' ? 'selected' : ''; ?>>사용 안 함</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">카테고리 사용</label>
                        <select name="use_category" class="form-select">
                            <option value="Y" <?php echo ($board['use_category'] ?? 'N') === 'Y' ? 'selected' : ''; ?>>사용</option>
                            <option value="N" <?php echo ($board['use_category'] ?? 'N') === 'N' ? 'selected' : ''; ?>>사용 안 함</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">상태</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo ($board['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>활성</option>
                            <option value="inactive" <?php echo ($board['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>비활성</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">저장</button>
                        <a href="/admin/boards" class="btn btn-secondary">목록</a>
                        <a href="/bbs/<?php echo $board['board_id']; ?>" class="btn btn-info" target="_blank">게시판 보기</a>
                        <button type="button" onclick="deleteBoard()" class="btn btn-danger ms-auto">게시판 삭제</button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <p>게시판을 찾을 수 없습니다.</p>
            <a href="/admin/boards" class="btn btn-primary">게시판 목록으로</a>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.getElementById('boardForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(e.target.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message || '저장되었습니다.');
            window.location.reload();
        } else {
            alert(result.message || '저장 실패');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    }
});

function deleteBoard() {
    if (!confirm('정말로 이 게시판을 삭제하시겠습니까?')) {
        return;
    }
    
    fetch(window.location.pathname, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            csrf_token: '<?php echo generateCsrfToken(); ?>'
        })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('삭제되었습니다.');
            window.location.href = '/admin/boards';
        } else {
            alert(result.message || '삭제 실패');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('오류가 발생했습니다.');
    });
}
</script>
</body>
</html>
