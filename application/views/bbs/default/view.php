<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($post['subject']); ?> - <?php echo xssFilter($board['board_name']); ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/board.css">
</head>
<body>
    <?php include __DIR__ . '/../../_header.php'; ?>
    
    <main class="container board-container">
        <div class="board-header">
            <h1><?php echo xssFilter($board['board_name']); ?></h1>
        </div>
        
        <!-- 게시물 내용 -->
        <article class="post-view">
            <div class="post-header">
                <h2 class="post-title">
                    <?php if ($post['is_notice'] === 'Y'): ?>
                        <span class="badge-notice">공지</span>
                    <?php endif; ?>
                    <?php echo xssFilter($post['subject']); ?>
                </h2>
                <div class="post-meta">
                    <span class="post-author">👤 <?php echo xssFilter($post['writer']); ?></span>
                    <span class="post-date">📅 <?php echo formatDate($post['created_at'], 'Y-m-d H:i'); ?></span>
                    <span class="post-views">👁️ <?php echo number_format($post['views']); ?></span>
                    <?php if ($post['comments'] > 0): ?>
                        <span class="post-comments">💬 <?php echo $post['comments']; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="post-content">
                <?php echo nl2br(xssFilter($post['content'])); ?>
            </div>
            
            <!-- 첨부파일 -->
            <?php if (!empty($files)): ?>
            <div class="post-files">
                <h4>📎 첨부파일 (<?php echo count($files); ?>)</h4>
                <ul class="file-list">
                    <?php foreach ($files as $file): ?>
                    <li class="file-item">
                        <a href="/bbs/download/<?php echo $file['uid']; ?>" class="file-link">
                            <?php if (strpos($file['file_type'], 'image/') === 0): ?>
                                <span class="file-icon">🖼️</span>
                            <?php elseif (strpos($file['file_type'], 'pdf') !== false): ?>
                                <span class="file-icon">📄</span>
                            <?php elseif (strpos($file['file_type'], 'zip') !== false): ?>
                                <span class="file-icon">📦</span>
                            <?php else: ?>
                                <span class="file-icon">📎</span>
                            <?php endif; ?>
                            <span class="file-name"><?php echo xssFilter($file['file_name']); ?></span>
                            <span class="file-info">
                                (<?php echo formatFileSize($file['file_size']); ?> / 
                                다운로드: <?php echo number_format($file['download_count']); ?>)
                            </span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="post-footer">
                <div class="post-actions">
                    <a href="/bbs/<?php echo $board['board_id']; ?>" class="btn">목록</a>
                    
                    <?php if (isLoggedIn() && (isset($_SESSION['user_id']) && $post['writer_uid'] == $_SESSION['user_id']) || isAdmin()): ?>
                        <a href="/bbs/<?php echo $board['board_id']; ?>/edit/<?php echo $post['uid']; ?>" class="btn">수정</a>
                        <button onclick="deletePost()" class="btn btn-danger">삭제</button>
                    <?php endif; ?>
                </div>
                
                <div class="post-likes">
                    <button onclick="likePost()" class="btn-like" id="likeBtn">
                        ❤️ 좋아요 <span id="likeCount"><?php echo $post['likes'] ?? 0; ?></span>
                    </button>
                </div>
            </div>
        </article>
        
        <!-- 이전/다음 글 -->
        <nav class="post-nav">
            <div class="post-nav-item">
                <span class="post-nav-label">이전 글</span>
                <?php if ($prev): ?>
                    <a href="/bbs/<?php echo $board['board_id']; ?>/view/<?php echo $prev['uid']; ?>">
                        <?php echo cutString(xssFilter($prev['subject']), 50); ?>
                    </a>
                <?php else: ?>
                    <span class="no-post">이전 글이 없습니다.</span>
                <?php endif; ?>
            </div>
            <div class="post-nav-item">
                <span class="post-nav-label">다음 글</span>
                <?php if ($next): ?>
                    <a href="/bbs/<?php echo $board['board_id']; ?>/view/<?php echo $next['uid']; ?>">
                        <?php echo cutString(xssFilter($next['subject']), 50); ?>
                    </a>
                <?php else: ?>
                    <span class="no-post">다음 글이 없습니다.</span>
                <?php endif; ?>
            </div>
        </nav>
        
        <!-- 댓글 섹션 -->
        <?php if ($board['use_comment'] === 'Y'): ?>
        <section class="comments-section">
            <h3 class="comments-title">💬 댓글 <?php echo count($comments); ?>개</h3>
            
            <!-- 댓글 목록 -->
            <div class="comment-list">
                <?php if (!empty($comments)): ?>
                    <?php foreach ($comments as $comment): ?>
                    <div class="comment-item" data-comment-id="<?php echo $comment['uid']; ?>">
                        <div class="comment-header">
                            <span class="comment-writer">
                                <?php echo xssFilter($comment['name']); ?>
                                <?php if ($comment['member_uid'] == $post['writer_uid']): ?>
                                    <span class="badge-author">작성자</span>
                                <?php endif; ?>
                            </span>
                            <span class="comment-date"><?php echo timeAgo($comment['reg_date']); ?></span>
                        </div>
                        <div class="comment-content">
                            <?php echo nl2br(xssFilter($comment['content'])); ?>
                        </div>
                        <?php if (isLoggedIn() && (isset($_SESSION['user_id']) && $comment['member_uid'] == $_SESSION['user_id']) || isAdmin()): ?>
                        <div class="comment-actions">
                            <button onclick="deleteComment(<?php echo $comment['uid']; ?>)" class="btn-comment-delete">삭제</button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-comments">첫 번째 댓글을 작성해보세요!</p>
                <?php endif; ?>
            </div>
            
            <!-- 댓글 작성 폼 -->
            <?php if ($board['comment_level'] <= ($_SESSION['level'] ?? 0)): ?>
            <div class="comment-form">
                <h4>댓글 작성</h4>
                <form id="commentForm" onsubmit="submitComment(event)">
                    <textarea name="content" id="commentContent" placeholder="댓글을 입력하세요..." required></textarea>
                    <div class="comment-form-footer">
                        <button type="submit" class="btn btn-primary">댓글 등록</button>
                    </div>
                </form>
            </div>
            <?php else: ?>
            <div class="comment-form">
                <p class="login-required">댓글을 작성하려면 <a href="/member/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">로그인</a>이 필요합니다.</p>
            </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>
    </main>
    
    <?php include __DIR__ . '/../../_footer.php'; ?>
    
    <script>
    // 게시물 삭제
    function deletePost() {
        if (!confirm('정말 삭제하시겠습니까?')) return;
        
        fetch('/bbs/<?php echo $board['board_id']; ?>/delete/<?php echo $post['uid']; ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.href = data.redirect;
            }
        })
        .catch(err => {
            alert('삭제 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    
    // 댓글 작성
    function submitComment(e) {
        e.preventDefault();
        
        const content = document.getElementById('commentContent').value;
        
        fetch('/bbs/<?php echo $board['board_id']; ?>/comment/<?php echo $post['uid']; ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'content=' + encodeURIComponent(content)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        })
        .catch(err => {
            alert('댓글 등록 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    
    // 댓글 삭제
    function deleteComment(commentId) {
        if (!confirm('댓글을 삭제하시겠습니까?')) return;
        
        fetch('/bbs/<?php echo $board['board_id']; ?>/comment/<?php echo $post['uid']; ?>/' + commentId, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'}
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        })
        .catch(err => {
            alert('삭제 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    
    // 좋아요
    function likePost() {
        fetch('/bbs/<?php echo $board['board_id']; ?>/like/<?php echo $post['uid']; ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('likeCount').textContent = data.likes;
                document.getElementById('likeBtn').classList.add('liked');
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            alert('좋아요 처리 중 오류가 발생했습니다.');
            console.error(err);
        });
    }
    </script>
</body>
</html>
