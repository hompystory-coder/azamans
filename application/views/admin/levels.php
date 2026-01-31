<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title); ?> - 관리자</title>
    
    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard-dynamic-subset.css" />
    <link href="https://cdn.jsdelivr.net/gh/sunn-us/SUIT/fonts/variable/woff2/SUIT-Variable.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/public/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0">
                    <i class="fas fa-layer-group text-main me-2"></i>
                    <?php echo xssFilter($title); ?>
                </h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLevelModal">
                    <i class="fas fa-plus me-1"></i>등급 추가
                </button>
            </div>
            
            <!-- 회원 등급 목록 -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">번호</th>
                                    <th width="100">등급값</th>
                                    <th>등급명</th>
                                    <th width="150">포인트 범위</th>
                                    <th width="150">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($levels)): ?>
                                    <?php 
                                    $num = count($levels);
                                    foreach ($levels as $level): 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $num--; ?></td>
                                        <td class="text-center">
                                            <span class="badge <?php 
                                                echo $level['level'] >= 9 ? 'bg-danger' : 
                                                    ($level['level'] >= 7 ? 'bg-warning' : 
                                                    ($level['level'] >= 5 ? 'bg-success' : 'bg-secondary')); 
                                            ?>">
                                                Lv.<?php echo $level['level']; ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold"><?php echo xssFilter($level['level_name']); ?></td>
                                        <td class="text-center">
                                            <?php echo number_format($level['point_min']); ?> ~ <?php echo number_format($level['point_max']); ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary me-1"
                                                    onclick='editLevel(<?php echo json_encode($level); ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteLevel(<?php echo $level['uid']; ?>, '<?php echo xssFilter($level['level_name']); ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            등록된 회원 등급이 없습니다.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- 등급 추가 모달 -->
    <div class="modal fade" id="createLevelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle text-main me-2"></i>회원 등급 추가
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createLevelForm">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">등급값 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="level" required min="1" max="99">
                            <small class="text-muted">1~99 사이의 숫자 (9 이상은 관리자)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">등급명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="level_name" required placeholder="예) 정회원">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">최소 포인트</label>
                            <input type="number" class="form-control" name="point_min" value="0" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">최대 포인트</label>
                            <input type="number" class="form-control" name="point_max" value="1000" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-primary" onclick="createLevel()">
                        <i class="fas fa-check me-1"></i>추가
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
                    <h5 class="modal-title">
                        <i class="fas fa-edit text-main me-2"></i>회원 등급 수정
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editLevelForm">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="uid" id="edit_uid">
                        <div class="mb-3">
                            <label class="form-label">등급값</label>
                            <input type="number" class="form-control" id="edit_level" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">등급명 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="level_name" id="edit_level_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">최소 포인트</label>
                            <input type="number" class="form-control" name="point_min" id="edit_point_min" min="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">최대 포인트</label>
                            <input type="number" class="form-control" name="point_max" id="edit_point_max" min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-primary" onclick="updateLevel()">
                        <i class="fas fa-check me-1"></i>수정
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function createLevel() {
        const form = document.getElementById('createLevelForm');
        const formData = new FormData(form);
        
        fetch('/admin/member/levels', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('등급이 추가되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('등급 추가 중 오류가 발생했습니다.');
        });
    }
    
    function editLevel(level) {
        document.getElementById('edit_uid').value = level.uid;
        document.getElementById('edit_level').value = level.level;
        document.getElementById('edit_level_name').value = level.level_name;
        document.getElementById('edit_point_min').value = level.point_min;
        document.getElementById('edit_point_max').value = level.point_max;
        
        new bootstrap.Modal(document.getElementById('editLevelModal')).show();
    }
    
    function updateLevel() {
        const form = document.getElementById('editLevelForm');
        const formData = new FormData(form);
        
        fetch('/admin/member/levels', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('등급이 수정되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('등급 수정 중 오류가 발생했습니다.');
        });
    }
    
    function deleteLevel(uid, name) {
        if (!confirm(`'${name}' 등급을 삭제하시겠습니까?`)) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('uid', uid);
        
        fetch('/admin/member/levels', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('등급이 삭제되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('등급 삭제 중 오류가 발생했습니다.');
        });
    }
    </script>
</body>
</html>
