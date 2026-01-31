<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title); ?> - 관리자</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-header">
            <h1><?php echo xssFilter($title); ?></h1>
            <div class="breadcrumb">
                <a href="/admin">대시보드</a> &gt; 
                <a href="/admin/boards">게시판 관리</a> &gt; 
                <span>게시판 설정</span>
            </div>
        </header>
        
        <?php if (!empty($board)): ?>
        <div class="detail-container">
            <form id="boardForm" method="POST" action="/admin/board/<?php echo $board['uid']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="detail-section">
                    <h2>기본 정보</h2>
                    
                    <div class="form-group">
                        <label>UID</label>
                        <input type="text" value="<?php echo $board['uid']; ?>" readonly class="readonly">
                    </div>
                    
                    <div class="form-group">
                        <label>게시판 ID</label>
                        <input type="text" value="<?php echo xssFilter($board['board_id']); ?>" readonly class="readonly">
                        <small>게시판 주소: /bbs/<?php echo $board['board_id']; ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label>게시판 이름 *</label>
                        <input type="text" name="board_name" value="<?php echo xssFilter($board['board_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>스킨</label>
                        <select name="board_skin">
                            <option value="default" <?php echo ($board['board_skin'] ?? 'default') === 'default' ? 'selected' : ''; ?>>기본형</option>
                            <option value="gallery" <?php echo ($board['board_skin'] ?? 'default') === 'gallery' ? 'selected' : ''; ?>>갤러리형</option>
                            <option value="blog" <?php echo ($board['board_skin'] ?? 'default') === 'blog' ? 'selected' : ''; ?>>블로그형</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>페이지당 게시물 수</label>
                        <input type="number" name="posts_per_page" value="<?php echo $board['posts_per_page'] ?? 20; ?>" min="10" max="100">
                    </div>
                </div>
                
                <div class="detail-section">
                    <h2>권한 설정</h2>
                    
                    <div class="form-group">
                        <label>읽기 권한</label>
                        <select name="read_level">
                            <option value="1" <?php echo ($board['read_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="5" <?php echo ($board['read_level'] ?? 1) == 5 ? 'selected' : ''; ?>>정회원 (5)</option>
                            <option value="9" <?php echo ($board['read_level'] ?? 1) == 9 ? 'selected' : ''; ?>>관리자 (9)</option>
                        </select>
                        <small>이 등급 이상만 게시판을 볼 수 있습니다.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>쓰기 권한</label>
                        <select name="write_level">
                            <option value="1" <?php echo ($board['write_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="5" <?php echo ($board['write_level'] ?? 1) == 5 ? 'selected' : ''; ?>>정회원 (5)</option>
                            <option value="9" <?php echo ($board['write_level'] ?? 1) == 9 ? 'selected' : ''; ?>>관리자 (9)</option>
                        </select>
                        <small>이 등급 이상만 글을 작성할 수 있습니다.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>댓글 권한</label>
                        <select name="comment_level">
                            <option value="1" <?php echo ($board['comment_level'] ?? 1) == 1 ? 'selected' : ''; ?>>일반 (1)</option>
                            <option value="5" <?php echo ($board['comment_level'] ?? 1) == 5 ? 'selected' : ''; ?>>정회원 (5)</option>
                            <option value="9" <?php echo ($board['comment_level'] ?? 1) == 9 ? 'selected' : ''; ?>>관리자 (9)</option>
                        </select>
                        <small>이 등급 이상만 댓글을 작성할 수 있습니다.</small>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h2>기능 설정</h2>
                    
                    <div class="form-group">
                        <label>댓글 사용</label>
                        <select name="use_comment">
                            <option value="Y" <?php echo ($board['use_comment'] ?? 'Y') === 'Y' ? 'selected' : ''; ?>>사용</option>
                            <option value="N" <?php echo ($board['use_comment'] ?? 'Y') === 'N' ? 'selected' : ''; ?>>사용 안 함</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>카테고리 사용</label>
                        <select name="use_category">
                            <option value="Y" <?php echo ($board['use_category'] ?? 'N') === 'Y' ? 'selected' : ''; ?>>사용</option>
                            <option value="N" <?php echo ($board['use_category'] ?? 'N') === 'N' ? 'selected' : ''; ?>>사용 안 함</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>상태</label>
                        <select name="status">
                            <option value="active" <?php echo ($board['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>활성</option>
                            <option value="inactive" <?php echo ($board['status'] ?? 'active') === 'inactive' ? 'selected' : ''; ?>>비활성</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">저장</button>
                    <a href="/admin/boards" class="btn-secondary">목록</a>
                    <a href="/bbs/<?php echo $board['board_id']; ?>" class="btn-info" target="_blank">게시판 보기</a>
                    <button type="button" onclick="deleteBoard()" class="btn-danger">게시판 삭제</button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="error-message">
            <p>게시판을 찾을 수 없습니다.</p>
            <a href="/admin/boards" class="btn-primary">게시판 목록으로</a>
        </div>
        <?php endif; ?>
    </main>
    
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
        if (!confirm('정말 이 게시판을 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없으며, 모든 게시물도 함께 삭제됩니다.')) {
            return;
        }
        
        const uid = <?php echo $board['uid'] ?? 0; ?>;
        
        fetch(`/admin/board/${uid}`, {
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
