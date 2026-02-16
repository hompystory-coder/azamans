<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-file-alt text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <!-- 검색 -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="board_id" class="form-select">
                            <option value="">전체 뉴스</option>
                            <?php foreach ($newsList as $board): ?>
                            <option value="<?php echo $board['board_id']; ?>" 
                                    <?php echo ($selectedBoard == $board['board_id']) ? 'selected' : ''; ?>>
                                <?php echo xssFilter($board['board_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" 
                               placeholder="제목, 작성자 검색" value="<?php echo xssFilter($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>검색
                        </button>
                        <a href="/admin/newsposts" class="btn btn-secondary">
                            <i class="fas fa-redo me-1"></i>초기화
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 뉴스 목록 -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="80">번호</th>
                                <th width="100">뉴스</th>
                                <th>제목</th>
                                <th width="100">작성자</th>
                                <th width="80">조회</th>
                                <th width="150">작성일</th>
                                <th width="100">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($posts)): ?>
                                <?php $num = $total - (($currentPage - 1) * 20); ?>
                                <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td class="text-center"><?php echo $num--; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo xssFilter($post['bbs_name'] ?? $post['bbs_id'] ?? ''); ?></span></td>
                                    <td>
                                        <a href="/news/<?php echo $post['bbs_id']; ?>/view/<?php echo $post['uid']; ?>" 
                                           target="_blank" class="text-decoration-none">
                                            <?php echo xssFilter($post['title'] ?? '제목 없음'); ?>
                                        </a>
                                    </td>
                                    <td><?php echo xssFilter($post['name'] ?? ''); ?></td>
                                    <td class="text-center"><?php echo number_format($post['view_count'] ?? 0); ?></td>
                                    <td class="text-center small"><?php echo date('Y-m-d H:i', strtotime($post['reg_date'] ?? 'now')); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deletePost(<?php echo $post['uid']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">뉴스이 없습니다.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&board_id=<?php echo $selectedBoard; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<script>
function deletePost(uid) {
    if (!confirm('정말로 삭제하시겠습니까?')) return;
    
    fetch('/admin/deletePost/' + uid, { 
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('삭제되었습니다.');
            location.reload();
        } else {
            alert('오류: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('삭제 중 오류가 발생했습니다.');
    });
}
</script>
</body>
</html>
