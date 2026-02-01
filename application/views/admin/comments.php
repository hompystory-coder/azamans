<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-comments text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <input type="text" name="search" class="form-control" placeholder="내용, 작성자 검색" value="<?php echo xssFilter($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i>검색</button>
                        <a href="/admin/comments" class="btn btn-secondary"><i class="fas fa-redo me-1"></i>초기화</a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80">번호</th>
                                <th width="100">게시판</th>
                                <th>게시물</th>
                                <th>댓글 내용</th>
                                <th width="100">작성자</th>
                                <th width="150">작성일</th>
                                <th width="100">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($comments)): ?>
                                <?php $num = $total - (($current_page - 1) * 20); ?>
                                <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td class="text-center"><?php echo $num--; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo xssFilter($comment['board_id']); ?></span></td>
                                    <td><small><?php echo xssFilter(mb_substr($comment['post_subject'], 0, 20)); ?></small></td>
                                    <td><?php echo xssFilter(mb_substr($comment['content'], 0, 50)); ?></td>
                                    <td><?php echo xssFilter($comment['writer']); ?></td>
                                    <td class="text-center small"><?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteComment(<?php echo $comment['uid']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted">댓글이 없습니다.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white">
                <nav><ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                </ul></nav>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
function deleteComment(uid) {
    if (!confirm('댓글을 삭제하시겠습니까?')) return;
    fetch('/admin/comments/' + uid, { method: 'DELETE' })
        .then(res => res.json())
        .then(data => {
            if (data.success) { alert('삭제되었습니다.'); location.reload(); }
            else { alert('오류: ' + data.message); }
        });
}
</script>
</body></html>
