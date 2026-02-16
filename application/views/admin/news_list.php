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
                    <i class="fas fa-list-alt text-main me-2"></i>
                    <?php echo xssFilter($title); ?>
                </h1>
                <div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBoardModal">
                        <i class="fas fa-plus me-1"></i>새 뉴스 만들기
                    </button>
                </div>
            </div>
            
            <!-- 통계 카드 -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">전체 뉴스</small>
                                    <h4 class="mb-0 fw-bold"><?php echo count($newsList ?? []); ?></h4>
                                </div>
                                <i class="fas fa-list-alt fa-2x text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">활성 뉴스</small>
                                    <h4 class="mb-0 fw-bold text-success">
                                        <?php 
                                        $active = 0;
                                        foreach ($newsList as $b) {
                                            if ($b['status'] == 'active') $active++;
                                        }
                                        echo $active; 
                                        ?>
                                    </h4>
                                </div>
                                <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">전체 게시물</small>
                                    <h4 class="mb-0 fw-bold">
                                        <?php 
                                        $totalPosts = 0;
                                        foreach ($newsList as $b) {
                                            $totalPosts += $b['post_count'] ?? 0;
                                        }
                                        echo number_format($totalPosts); 
                                        ?>
                                    </h4>
                                </div>
                                <i class="fas fa-file-alt fa-2x text-info opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">현재 시간</small>
                                    <h6 class="mb-0"><?php echo date('H:i'); ?></h6>
                                </div>
                                <i class="far fa-clock fa-2x text-warning opacity-25"></i>
                            </div>
                        </div>
                    </div>
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
                                    <th>뉴스 ID</th>
                                    <th>뉴스명</th>
                                    <th width="120">스킨</th>
                                    <th width="100">게시물 수</th>
                                    <th width="100">상태</th>
                                    <th width="100">권한</th>
                                    <th width="150">생성일</th>
                                    <th width="120">관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($newsList)): ?>
                                    <?php 
                                    $num = count($newsList);
                                    foreach ($newsList as $board): 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $num--; ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo xssFilter($board['news_id']); ?></span>
                                        </td>
                                        <td>
                                            <a href="/news/<?php echo $board['news_id']; ?>" 
                                               class="text-decoration-none fw-bold" 
                                               target="_blank">
                                                <?php echo xssFilter($board['news_name']); ?>
                                                <i class="fas fa-external-link-alt ms-1 small text-muted"></i>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info"><?php echo xssFilter($board['news_skin'] ?? 'default'); ?></span>
                                        </td>
                                        <td class="text-end fw-bold"><?php echo number_format($board['post_count'] ?? 0); ?></td>
                                        <td class="text-center">
                                            <?php if ($board['status'] == 'active'): ?>
                                                <span class="badge bg-success">활성</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">비활성</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                읽기:<?php echo $board['read_level']; ?> / 
                                                쓰기:<?php echo $board['write_level']; ?>
                                            </small>
                                        </td>
                                        <td class="text-center small text-muted">
                                            <?php echo isset($board['created_at']) ? date('Y-m-d', strtotime($board['created_at'])) : '-'; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="/admin/news/<?php echo $board['uid']; ?>" 
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteBoard(<?php echo $board['uid']; ?>, '<?php echo xssFilter($board['news_name']); ?>')">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                            뉴스이 없습니다. 새 뉴스을 만들어보세요!
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
    
    <!-- 뉴스 생성 모달 -->
    <div class="modal fade" id="createBoardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle text-main me-2"></i>새 뉴스 만들기
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createBoardForm">
                        <div class="mb-3">
                            <label class="form-label">뉴스 ID <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="news_id" 
                                   required 
                                   placeholder="영문 소문자, 숫자, 하이픈만 가능 (예: free, notice)">
                            <small class="text-muted">URL에 사용될 ID입니다. (예: /news/free)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">뉴스명 <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   name="news_name" 
                                   required 
                                   placeholder="예) 자유뉴스, 공지사항">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">스킨</label>
                            <select class="form-select" name="news_skin">
                                <option value="default">기본 (default)</option>
                                <option value="gallery">갤러리 (gallery)</option>
                                <option value="blog">블로그 (blog)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">페이지당 게시물 수</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="posts_per_page" 
                                   value="20" 
                                   min="10" 
                                   max="100">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">읽기 권한</label>
                                <select class="form-select" name="read_level">
                                    <option value="1">Lv.1 (전체)</option>
                                    <option value="5">Lv.5 (정회원)</option>
                                    <option value="9">Lv.9 (관리자)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">쓰기 권한</label>
                                <select class="form-select" name="write_level">
                                    <option value="1">Lv.1 (전체)</option>
                                    <option value="5">Lv.5 (정회원)</option>
                                    <option value="9">Lv.9 (관리자)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">댓글 권한</label>
                                <select class="form-select" name="comment_level">
                                    <option value="1">Lv.1 (전체)</option>
                                    <option value="5">Lv.5 (정회원)</option>
                                    <option value="9">Lv.9 (관리자)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">댓글 사용</label>
                                <select class="form-select" name="use_comment">
                                    <option value="Y">사용</option>
                                    <option value="N">사용 안함</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">카테고리 사용</label>
                                <select class="form-select" name="use_category">
                                    <option value="N">사용 안함</option>
                                    <option value="Y">사용</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="button" class="btn btn-primary" onclick="createBoard()">
                        <i class="fas fa-check me-1"></i>생성
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function createBoard() {
        const form = document.getElementById('createBoardForm');
        const formData = new FormData(form);
        
        fetch('/admin/news', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('뉴스이 생성되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('뉴스 생성 중 오류가 발생했습니다.');
        });
    }
    
    function deleteBoard(uid, name) {
        if (!confirm(`'${name}' 뉴스을 삭제하시겠습니까?\n뉴스과 모든 게시물이 삭제됩니다.`)) {
            return;
        }
        
        fetch(`/admin/news/${uid}`, {
            method: 'DELETE'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('뉴스이 삭제되었습니다.');
                location.reload();
            } else {
                alert('오류: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('뉴스 삭제 중 오류가 발생했습니다.');
        });
    }
    </script>
</body>
</html>
