<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-sitemap text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="sitemapForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">사이트맵에서 제외할 게시판</label>
                        <?php foreach ($boards as $board): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="sitemap_exclude[]" 
                                   value="<?php echo $board['board_id']; ?>"
                                   <?php echo in_array($board['board_id'], $sitemap_exclude) ? 'checked' : ''; ?>>
                            <label class="form-check-label"><?php echo xssFilter($board['board_name']); ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="saveSitemap()">
                            <i class="fas fa-save me-1"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
function saveSitemap() {
    const form = document.getElementById('sitemapForm');
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
