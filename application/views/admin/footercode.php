<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-code text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>푸터 코드란?</strong> 모든 페이지의 &lt;/body&gt; 태그 직전에 삽입될 HTML/JavaScript 코드입니다.
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form id="footerCodeForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">HTML/JavaScript 코드</label>
                        <textarea class="form-control font-monospace" name="footer_code" rows="15"><?php echo htmlspecialchars($footer_code); ?></textarea>
                        <small class="text-muted">이 코드는 모든 페이지의 &lt;/body&gt; 태그 직전에 삽입됩니다.</small>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" onclick="location.reload()">
                            <i class="fas fa-redo me-1"></i>초기화
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveCode()">
                            <i class="fas fa-save me-1"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
function saveCode() {
    const form = document.getElementById('footerCodeForm');
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
