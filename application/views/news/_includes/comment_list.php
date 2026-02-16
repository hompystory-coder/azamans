<?php
/**
 * 댓글 목록 공통 include
 * 모든 게시판 view.php에서 사용
 */
?>
<?php if (!empty($comments)): ?>
<ul class="comments-list">
    <?php foreach ($comments as $comment): ?>
    <li class="comment-item">
        <div class="comment-header">
            <span class="comment-author"><?php echo xssFilter($comment['name']); ?></span>
            <div class="comment-meta">
                <span class="comment-date"><?php echo date('Y-m-d H:i', strtotime($comment['reg_date'])); ?></span>
                <?php if ($comment['is_secret'] === 'Y'): ?>
                <span class="badge badge-secondary">비밀댓글</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="comment-content">
            <?php echo nl2br(xssFilter($comment['content'])); ?>
        </div>
        
        <?php if (isLoggedIn() && ($_SESSION['user_id'] == $comment['member_uid'] || isAdmin())): ?>
        <div class="comment-actions">
            <button onclick="deleteComment('<?php echo $news['news_id']; ?>', '<?php echo $post['uid']; ?>', '<?php echo $comment['uid']; ?>')" 
                    class="btn-delete-comment">
                삭제
            </button>
        </div>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p class="no-comments">첫 번째 댓글을 작성해보세요!</p>
<?php endif; ?>
