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
        <?php include __DIR__ . '/_sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold mb-0">
                    <i class="fas fa-coins text-main me-2"></i>
                    <?php echo xssFilter($title); ?>
                </h1>
                <div class="text-muted">
                    <i class="far fa-clock me-1"></i>
                    <?php echo date('Y년 m월 d일 H:i'); ?>
                </div>
            </div>
            
            <!-- 포인트 지급 카드 -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-gift text-main me-2"></i>포인트 지급
                    </h5>
                </div>
                <div class="card-body">
                    <form id="pointForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">회원 선택 <span class="text-danger">*</span></label>
                                <select class="form-select" name="member_uid" id="memberSelect" required>
                                    <option value="">회원을 선택하세요</option>
                                    <?php foreach ($members as $member): ?>
                                    <option value="<?php echo $member['uid']; ?>" 
                                            data-point="<?php echo $member['point']; ?>">
                                        <?php echo xssFilter($member['user_id']); ?> 
                                        (<?php echo xssFilter($member['name']); ?>) 
                                        - 보유: <?php echo number_format($member['point']); ?>P
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">지급 포인트 <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="points" required 
                                       placeholder="양수: 지급, 음수: 차감">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-bold">현재 보유</label>
                                <input type="text" class="form-control" id="currentPoint" readonly 
                                       value="0 P" style="background-color: #f8f9fa;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">지급 사유</label>
                            <input type="text" class="form-control" name="reason" 
                                   placeholder="예) 이벤트 당첨, 우수 회원 보상">
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-redo me-1"></i>초기화
                            </button>
                            <button type="button" class="btn btn-primary" onclick="givePoints()">
                                <i class="fas fa-check me-1"></i>지급
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- 회원 목록 -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-users text-main me-2"></i>회원 포인트 현황
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">번호</th>
                                    <th>아이디</th>
                                    <th>이름</th>
                                    <th width="150">포인트</th>
                                    <th width="100">등급</th>
                                    <th width="100">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($members)): ?>
                                    <?php 
                                    $num = count($members);
                                    foreach ($members as $member): 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $num--; ?></td>
                                        <td><?php echo xssFilter($member['user_id']); ?></td>
                                        <td><?php echo xssFilter($member['name']); ?></td>
                                        <td class="text-end fw-bold text-primary">
                                            <?php echo number_format($member['point']); ?> P
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">Lv.<?php echo $member['level'] ?? 1; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary"
                                                    onclick="selectMember(<?php echo $member['uid']; ?>)">
                                                <i class="fas fa-hand-holding-usd"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            회원이 없습니다.
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
    
    <script>
    // 회원 선택 시 현재 포인트 표시
    document.getElementById('memberSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const point = selected.getAttribute('data-point') || 0;
        document.getElementById('currentPoint').value = new Intl.NumberFormat().format(point) + ' P';
    });
    
    function selectMember(uid) {
        document.getElementById('memberSelect').value = uid;
        document.getElementById('memberSelect').dispatchEvent(new Event('change'));
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    function givePoints() {
        const form = document.getElementById('pointForm');
        const formData = new FormData(form);
        
        if (!formData.get('member_uid')) {
            alert('회원을 선택해주세요.');
            return;
        }
        
        if (!formData.get('points')) {
            alert('포인트를 입력해주세요.');
            return;
        }
        
        const points = parseInt(formData.get('points'));
        if (points === 0) {
            alert('0 포인트는 지급할 수 없습니다.');
            return;
        }
        
        const confirmMsg = points > 0 
            ? `${points} 포인트를 지급하시겠습니까?` 
            : `${Math.abs(points)} 포인트를 차감하시겠습니까?`;
        
        if (!confirm(confirmMsg)) {
            return;
        }
        
        fetch('/admin/points', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(points > 0 ? '포인트가 지급되었습니다.' : '포인트가 차감되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('포인트 처리 중 오류가 발생했습니다.');
        });
    }
    
    function resetForm() {
        document.getElementById('pointForm').reset();
        document.getElementById('currentPoint').value = '0 P';
    }
    </script>
</body>
</html>
