<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?= xssFilter($title) ?></h2>
                <p class="text-muted mb-0"><?= date('Y년 m월 d일 H:i') ?></p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLevelModal">
                <i class="fas fa-plus me-2"></i>등급 추가
            </button>
        </div>
        
        <!-- 안내 -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>회원 등급이란?</strong> 회원의 권한과 혜택을 구분하는 시스템입니다. 레벨 번호가 높을수록 상위 등급입니다.
        </div>
        
        <!-- 등급 목록 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-layer-group"></i> 회원 등급 목록
                </h5>
                <span class="badge bg-primary">총 <?= count($levels ?? []) ?>개 등급</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%">레벨</th>
                                <th style="width: 20%">등급명</th>
                                <th style="width: 15%">아이콘</th>
                                <th style="width: 20%">포인트 범위</th>
                                <th style="width: 15%">회원 수</th>
                                <th style="width: 20%">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($levels)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    등록된 등급이 없습니다.
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($levels as $level): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary fs-6">
                                            Lv.<?= xssFilter($level['level']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= xssFilter($level['level_name']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($level['level_icon'])): ?>
                                            <i class="<?= xssFilter($level['level_icon']) ?> fa-2x text-warning"></i>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= number_format($level['point_min']) ?> ~ <?= number_format($level['point_max']) ?>pt
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= number_format($level['member_count'] ?? 0) ?>명
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editLevel(<?= $level['uid'] ?>)">
                                            <i class="fas fa-edit"></i> 수정
                                        </button>
                                        <?php if (($level['member_count'] ?? 0) == 0): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteLevel(<?= $level['uid'] ?>)">
                                            <i class="fas fa-trash"></i> 삭제
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </main>
</div>

<!-- 등급 추가 모달 -->
<div class="modal fade" id="addLevelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">등급 추가</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addLevelForm">
                    <div class="mb-3">
                        <label class="form-label">레벨 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="level" required min="1" max="99">
                        <small class="text-muted">1~99 사이의 숫자 (높을수록 상위 등급)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">등급명 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="level_name" required placeholder="예: 준회원, 정회원">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">아이콘 (Font Awesome)</label>
                        <input type="text" class="form-control" name="level_icon" placeholder="예: fas fa-star">
                        <small class="text-muted">Font Awesome 클래스명 입력</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">최소 포인트</label>
                            <input type="number" class="form-control" name="point_min" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">최대 포인트</label>
                            <input type="number" class="form-control" name="point_max" value="100">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="submitAddLevel()">
                    <i class="fas fa-save me-2"></i>저장
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 등급 수정 모달 -->
<div class="modal fade" id="editLevelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">등급 수정</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editLevelForm">
                    <input type="hidden" name="uid" id="edit_uid">
                    <div class="mb-3">
                        <label class="form-label">레벨 <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="level" id="edit_level" required min="1" max="99">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">등급명 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="level_name" id="edit_level_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">아이콘 (Font Awesome)</label>
                        <input type="text" class="form-control" name="level_icon" id="edit_level_icon">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">최소 포인트</label>
                            <input type="number" class="form-control" name="point_min" id="edit_point_min">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">최대 포인트</label>
                            <input type="number" class="form-control" name="point_max" id="edit_point_max">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="submitEditLevel()">
                    <i class="fas fa-save me-2"></i>수정
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function submitAddLevel() {
    const form = document.getElementById('addLevelForm');
    const formData = new FormData(form);
    
    fetch('/admin/levels/add', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('등급이 추가되었습니다.');
            location.reload();
        } else {
            alert(data.message || '등급 추가에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function editLevel(uid) {
    fetch(`/admin/levels/detail/${uid}`)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('edit_uid').value = data.level.uid;
            document.getElementById('edit_level').value = data.level.level;
            document.getElementById('edit_level_name').value = data.level.level_name;
            document.getElementById('edit_level_icon').value = data.level.level_icon || '';
            document.getElementById('edit_point_min').value = data.level.point_min;
            document.getElementById('edit_point_max').value = data.level.point_max;
            
            const modal = new bootstrap.Modal(document.getElementById('editLevelModal'));
            modal.show();
        }
    });
}

function submitEditLevel() {
    const form = document.getElementById('editLevelForm');
    const formData = new FormData(form);
    
    fetch('/admin/levels/update', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('등급이 수정되었습니다.');
            location.reload();
        } else {
            alert(data.message || '등급 수정에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}

function deleteLevel(uid) {
    if (!confirm('정말 이 등급을 삭제하시겠습니까?')) return;
    
    fetch('/admin/levels/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({uid: uid})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('등급이 삭제되었습니다.');
            location.reload();
        } else {
            alert(data.message || '등급 삭제에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
