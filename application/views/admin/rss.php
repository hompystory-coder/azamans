<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-rss text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="rssForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">RSS 추출 게시판</label>
                        <?php foreach ($boards as $board): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="rss_boards[]" 
                                   value="<?php echo $board['board_id']; ?>"
                                   <?php echo in_array($board['board_id'], $rss_boards) ? 'checked' : ''; ?>>
                            <label class="form-check-label"><?php echo xssFilter($board['board_name']); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">추출 기간 (일)</label>
                        <input type="number" class="form-control" name="rss_period" value="<?php echo $rss_period; ?>" min="1" max="365">
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="saveRss()">
                            <i class="fas fa-save me-1"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
function saveRss() {
    const form = document.getElementById('rssForm');
    const formData = new FormData(form);
    fetch('/admin/config/save', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { alert('저장되었습니다.'); location.reload(); }
            else { alert('오류: ' + data.message); }
        });
}
</script>
</body></html>
