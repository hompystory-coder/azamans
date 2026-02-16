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
                        <li class="breadcrumb-item"><a href="/admin/news">뉴스 관리</a></li>
                        <li class="breadcrumb-item active">뉴스 설정</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <?php if (!empty($news)): ?>
        <div class="card">
            <div class="card-body">
                <form id="newsForm" method="POST" action="/admin/news/<?php echo $news['uid']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <h5 class="card-title mb-4">기본 정보</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">UID</label>
                        <input type="text" class="form-control" value="<?php echo $news['uid']; ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">뉴스 ID</label>
                        <input type="text" class="form-control" value="<?php echo xssFilter($news['news_id']); ?>" readonly>
                        <small class="form-text text-muted">뉴스 주소: /news/<?php echo $news['news_id']; ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">뉴스명 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="news_name" value="<?php echo xssFilter($news['news_name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">스킨</label>
                        <select name="news_skin" class="form-select">
                            <option value="default" <?php echo ($news['news_skin'] ?? 'default') === 'default' ? 'selected' : ''; ?>>기본형</option>
                            <option value="gallery" <?php echo ($news['news_skin'] ?? 'default') === 'gallery' ? 'selected' : ''; ?>>갤러리형</option>
                            <option value="blog" <?php echo ($news['news_skin'] ?? 'default') === 'blog' ? 'selected' : ''; ?>>블로그형</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">페이지당 게시물 수</label>
                        <input type="number" class="form-control" name="posts_per_page" value="<?php echo $news['posts_per_page'] ?? 20; ?>" min="10" max="100">
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="card-title mb-4">권한 설정</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">읽기 권한</label>
                        <select name="read_level" class="form-select">
                            <option value="1" <?php echo ($news['read_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="2" <?php echo ($news['read_level'] ?? 1) == 2 ? 'selected' : ''; ?>>정회원 (2)</option>
                            <option value="5" <?php echo ($news['read_level'] ?? 1) == 5 ? 'selected' : ''; ?>>VIP (5)</option>
                            <option value="10" <?php echo ($news['read_level'] ?? 1) == 10 ? 'selected' : ''; ?>>관리자 (10)</option>
                        </select>
                        <small class="form-text text-muted">이 등급 이상만 뉴스를 볼 수 있습니다.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">쓰기 권한</label>
                        <select name="write_level" class="form-select">
                            <option value="1" <?php echo ($news['write_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="2" <?php echo ($news['write_level'] ?? 1) == 2 ? 'selected' : ''; ?>>정회원 (2)</option>
                            <option value="5" <?php echo ($news['write_level'] ?? 1) == 5 ? 'selected' : ''; ?>>VIP (5)</option>
                            <option value="10" <?php echo ($news['write_level'] ?? 1) == 10 ? 'selected' : ''; ?>>관리자 (10)</option>
                        </select>
                        <small class="form-text text-muted">이 등급 이상만 글을 작성할 수 있습니다.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">댓글 권한</label>
                        <select name="comment_level" class="form-select">
                            <option value="1" <?php echo ($news['comment_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="2" <?php echo ($news['comment_level'] ?? 1) == 2 ? 'selected' : ''; ?>>정회원 (2)</option>
                            <option value="5" <?php echo ($news['comment_level'] ?? 1) == 5 ? 'selected' : ''; ?>>VIP (5)</option>
                            <option value="10" <?php echo ($news['comment_level'] ?? 1) == 10 ? 'selected' : ''; ?>>관리자 (10)</option>
                        </select>
                        <small class="form-text text-muted">이 등급 이상만 댓글을 작성할 수 있습니다.</small>
                    </div>
                    
                    <hr class="my-4">
                    <h5 class="card-title mb-4">기능 설정</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">댓글 사용</label>
                        <select name="use_comment" class="form-select">
                            <option value="Y" <?php echo ($news['use_comment'] ?? 'Y') === 'Y' ? 'selected' : ''; ?>>사용</option>
                            <option value="N" <?php echo ($news['use_comment'] ?? 'Y') === 'N' ? 'selected' : ''; ?>>사용 안 함</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">카테고리 사용</label>
                        <select name="use_category" class="form-select">
                            <option value="Y" <?php echo ($news['use_category'] ?? 'N') === 'Y' ? 'selected' : ''; ?>>사용</option>
                            <option value="N" <?php echo ($news['use_category'] ?? 'N') === 'N' ? 'selected' : ''; ?>>사용 안 함</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">상태</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo ($news['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>활성</option>
                            <option value="inactive" <?php echo ($news['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>비활성</option>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">저장</button>
                        <a href="/admin/news" class="btn btn-secondary">목록</a>
                        <a href="/news/<?php echo $news['news_id']; ?>" class="btn btn-info" target="_blank">뉴스 보기</a>
                        <button type="button" onclick="deleteNews()" class="btn btn-danger ms-auto">뉴스 삭제</button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <p>뉴스를 찾을 수 없습니다.</p>
            <a href="/admin/news" class="btn btn-primary">뉴스 목록으로</a>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.getElementById('newsForm').addEventListener('submit', async (e) => {
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

function deleteNews() {
    if (!confirm('정말로 이 뉴스를 삭제하시겠습니까?')) {
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
            window.location.href = '/admin/news';
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
