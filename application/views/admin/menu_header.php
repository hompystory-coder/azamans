<?php include __DIR__ . '/_admin_header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <h2 class="mb-4">
                <i class="fas fa-bars me-2"></i>헤더 메뉴 관리
            </h2>
            
            <div class="row">
                <!-- 왼쪽: 메뉴 생성 폼 -->
                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-plus-circle me-2"></i>새 메뉴 만들기
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="menuCreateForm">
                                <div class="mb-3">
                                    <label for="menu_name" class="form-label">메뉴명 <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="menu_name" name="menu_name" placeholder="예: 공지사항, 자유게시판" required>
                                </div>
                                <div class="mb-3">
                                    <label for="menu_url" class="form-label">URL</label>
                                    <input type="text" class="form-control" id="menu_url" name="menu_url" placeholder="예: /bbs/notice" value="/">
                                    <small class="text-muted">비워두면 '/' 로 설정됩니다.</small>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-check me-2"></i>생성
                                    </button>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="fas fa-redo me-2"></i>초기화
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- 오른쪽: 메뉴 리스트 -->
                <div class="col-md-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>메뉴 목록
                            </h5>
                            <span class="badge bg-light text-dark">총 <?php echo count($menus); ?>개</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($menus)): ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    생성된 메뉴가 없습니다. 왼쪽에서 메뉴를 생성해주세요.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-arrows-alt me-2"></i>
                                    드래그하여 순서를 변경할 수 있습니다.
                                </div>
                                <ul id="menuList" class="list-group">
                                    <?php foreach ($menus as $menu): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center" data-id="<?php echo $menu['id']; ?>" style="cursor: move;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-grip-vertical text-muted me-3"></i>
                                                <div>
                                                    <strong><?php echo xssFilter($menu['menu_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-link me-1"></i><?php echo xssFilter($menu['menu_url']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <button class="btn btn-sm btn-danger delete-menu" data-id="<?php echo $menu['id']; ?>">
                                                <i class="fas fa-trash"></i> 삭제
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery UI for Sortable -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function() {
    // 메뉴 생성 폼 제출
    $('#menuCreateForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '/admin/createMenu',
            method: 'POST',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('메뉴 생성 중 오류가 발생했습니다.');
            }
        });
    });
    
    // 메뉴 삭제
    $(document).on('click', '.delete-menu', function() {
        if (!confirm('정말 삭제하시겠습니까?')) {
            return;
        }
        
        const menuId = $(this).data('id');
        
        $.ajax({
            url: '/admin/deleteMenu/' + menuId,
            method: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('메뉴 삭제 중 오류가 발생했습니다.');
            }
        });
    });
    
    // 드래그 앤 드롭으로 순서 변경
    <?php if (!empty($menus)): ?>
    $('#menuList').sortable({
        handle: '.fa-grip-vertical',
        update: function(event, ui) {
            const orders = [];
            $('#menuList li').each(function(index) {
                orders.push($(this).data('id'));
            });
            
            $.ajax({
                url: '/admin/updateMenuOrder',
                method: 'POST',
                data: { orders: orders },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('순서 변경 완료');
                    } else {
                        alert(response.message);
                        location.reload();
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('순서 변경 중 오류가 발생했습니다.');
                    location.reload();
                }
            });
        }
    });
    <?php endif; ?>
});
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
