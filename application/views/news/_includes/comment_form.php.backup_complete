<?php
/**
 * 댓글 작성 폼 공통 include
 * 모든 게시판 view.php에서 사용
 */
?>
<div class="comment-form">
    <?php if (isLoggedIn()): ?>
        <h4>댓글 작성</h4>
        <form id="commentForm" onsubmit="submitComment(event, '<?php echo $news['news_id']; ?>', '<?php echo $post['uid']; ?>')">
            <textarea name="content" id="commentContent" placeholder="댓글을 입력하세요..." required></textarea>
            <div class="comment-form-footer">
                <button type="submit" class="btn btn-primary">댓글 등록</button>
            </div>
        </form>
    <?php else: ?>
        <p class="login-required">
            댓글을 작성하려면 <a href="/member/login?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>">로그인</a>이 필요합니다.
        </p>
    <?php endif; ?>
</div>
