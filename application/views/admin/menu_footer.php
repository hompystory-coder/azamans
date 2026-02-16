<?php include __DIR__ . '/_admin_header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <h2 class="mb-4">
                <i class="fas fa-bars me-2"></i>푸터 메뉴 관리
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
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        콤마(,)로 구분하면 여러 메뉴를 한번에 생성할 수 있습니다.
                                    </small>
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
                                    <?php 
                                    // 1차 메뉴만 표시 (parent_id = 0)
                                    $topMenus = array_filter($menus, function($m) { return $m['parent_id'] == 0; });
                                    foreach ($topMenus as $menu): 
                                    ?>
                                        <li class="list-group-item" data-id="<?php echo $menu['id']; ?>" style="cursor: move;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <i class="fas fa-grip-vertical text-muted me-3"></i>
                                                    <div>
                                                        <strong><?php echo xssFilter($menu['menu_name']); ?></strong>
                                                        <span class="badge bg-info ms-2"><?php echo $menu['menu_type']; ?></span>
                                                        <?php if ($menu['use_redirect'] === 'Y'): ?>
                                                            <span class="badge bg-primary ms-1" title="리다이렉트 메뉴">
                                                                <i class="fas fa-external-link-alt"></i> 리다이렉트
                                                            </span>
                                                        <?php endif; ?>
                                                        <?php if ($menu['is_hidden'] === 'Y'): ?>
                                                            <span class="badge bg-warning text-dark ms-1">숨김</span>
                                                        <?php endif; ?>
                                                        <?php if ($menu['is_blocked'] === 'Y'): ?>
                                                            <span class="badge bg-danger ms-1">차단</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary add-submenu" data-id="<?php echo $menu['id']; ?>" title="서브메뉴 추가">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <a href="/admin/editFooterMenu/<?php echo $menu['id']; ?>" class="btn btn-sm btn-outline-success" title="수정">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger delete-menu" data-id="<?php echo $menu['id']; ?>" title="삭제">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <?php 
                                            // 서브메뉴 표시
                                            $subMenus = array_filter($menus, function($m) use ($menu) { return $m['parent_id'] == $menu['id']; });
                                            if (!empty($subMenus)): 
                                            ?>
                                                <ul class="list-group mt-2 ms-5">
                                                    <?php foreach ($subMenus as $sub): ?>
                                                        <li class="list-group-item">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-2"></i>
                                                                    <?php echo xssFilter($sub['menu_name']); ?>
                                                                    <span class="badge bg-secondary ms-2"><?php echo $sub['menu_type']; ?></span>
                                                                    <?php if ($sub['use_redirect'] === 'Y'): ?>
                                                                        <span class="badge bg-primary ms-1" title="리다이렉트 메뉴">
                                                                            <i class="fas fa-external-link-alt"></i> 리다이렉트
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="btn-group">
                                                                    <a href="/admin/editFooterMenu/<?php echo $sub['id']; ?>" class="btn btn-sm btn-outline-success">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <button class="btn btn-sm btn-outline-danger delete-menu" data-id="<?php echo $sub['id']; ?>">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
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
            url: '/admin/createFooterMenu',
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
            error: function(xhr, status, error) {
                console.error('Error:', xhr);
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response Text:', xhr.responseText);
                console.error('Status Code:', xhr.status);
                console.error('Ready State:', xhr.readyState);
                
                alert('메뉴 생성 중 오류가 발생했습니다.\n\n' +
                      'Status: ' + xhr.status + '\n' +
                      'Error: ' + error + '\n' +
                      'Response: ' + xhr.responseText.substring(0, 200));
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
            url: '/admin/deleteFooterMenu/' + menuId,
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
    
    // 서브메뉴 추가
    $(document).on('click', '.add-submenu', function() {
        const parentId = $(this).data('id');
        const menuName = prompt('서브메뉴 이름을 입력하세요:');
        
        if (!menuName || menuName.trim() === '') {
            return;
        }
        
        $.ajax({
            url: '/admin/addFooterSubmenu/' + parentId,
            method: 'POST',
            data: { menu_name: menuName.trim() },
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
                alert('서브메뉴 추가 중 오류가 발생했습니다.');
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
                url: '/admin/updateFooterMenuOrder',
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
