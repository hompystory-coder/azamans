<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-user-plus text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form id="joinConfigForm">
                    <div class="mb-4">
                        <label class="form-label fw-bold">이용약관</label>
                        <textarea class="form-control" name="terms_of_service" rows="10"><?php echo htmlspecialchars($terms_of_service); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">개인정보보호정책</label>
                        <textarea class="form-control" name="privacy_policy" rows="10"><?php echo htmlspecialchars($privacy_policy); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">청소년보호정책</label>
                        <textarea class="form-control" name="youth_protection" rows="10"><?php echo htmlspecialchars($youth_protection); ?></textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" onclick="saveConfig()">
                            <i class="fas fa-save me-1"></i>저장
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
function saveConfig() {
    const form = document.getElementById('joinConfigForm');
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
