<?php include __DIR__ . '/../../_header.php'; ?>

<main>
    <div class="container">
        <!-- 게시판 헤더 -->
        <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h2 class="mb-1 fw-bold">
                            <i class="fas fa-list text-main me-2"></i>
                            <?php echo xssFilter($board['board_name']); ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-file-alt me-1"></i>
                            전체 게시물: <strong><?php echo number_format($total); ?></strong>개
                        </p>
                    </div>
                    <?php if ($board['write_level'] <= ($_SESSION['level'] ?? 0)): ?>
                        <a href="/bbs/<?php echo $board['board_id']; ?>/write" class="btn btn-primary">
                            <i class="fas fa-pen me-2"></i>글쓰기
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 검색 폼 -->
        <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
            <div class="card-body">
                <form method="get" action="/bbs/<?php echo $board['board_id']; ?>" class="row g-3">
                    <?php if ($board['use_category'] === 'Y' && !empty($board['categories'])): ?>
                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">전체 카테고리</option>
                                <?php 
                                $categories = json_decode($board['categories'], true);
                                foreach ($categories as $cat):
                                ?>
                                    <option value="<?php echo xssFilter($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                                        <?php echo xssFilter($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="col-md">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="제목 또는 내용 검색" 
                                   value="<?php echo xssFilter($search ?? ''); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>검색
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- 게시물 목록 -->
        <div class="card border-0 shadow-sm animate__animated animate__fadeInUp">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80" class="text-center">번호</th>
                            <?php if ($board['use_category'] === 'Y'): ?>
                                <th width="120" class="text-center">카테고리</th>
                            <?php endif; ?>
                            <th>제목</th>
                            <th width="120" class="text-center">작성자</th>
                            <th width="80" class="text-center">조회</th>
                            <th width="120" class="text-center">작성일</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 공지사항 -->
                        <?php if (!empty($notices)): ?>
                            <?php foreach ($notices as $notice): ?>
                            <tr class="table-warning">
                                <td class="text-center">
                                    <i class="fas fa-bullhorn text-danger"></i>
                                </td>
                                <?php if ($board['use_category'] === 'Y'): ?>
                                    <td class="text-center">
                                        <span class="badge bg-danger"><?php echo xssFilter($notice['category'] ?? '-'); ?></span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <a href="/bbs/<?php echo $board['board_id']; ?>/view/<?php echo $notice['uid']; ?>" 
                                       class="text-decoration-none text-dark fw-bold">
                                        <i class="fas fa-bullhorn text-danger me-1"></i>
                                        <?php echo xssFilter($notice['subject']); ?>
                                        <?php if ($notice['comments'] > 0): ?>
                                            <span class="badge bg-primary ms-1"><?php echo $notice['comments']; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="text-center"><?php echo xssFilter($notice['writer']); ?></td>
                                <td class="text-center"><?php echo number_format($notice['views']); ?></td>
                                <td class="text-center"><?php echo date('Y-m-d', strtotime($notice['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- 일반 게시물 -->
                        <?php if (!empty($posts)): ?>
                            <?php 
                            $num = $total - (($current_page - 1) * $board['posts_per_page']);
                            foreach ($posts as $post): 
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $num--; ?></td>
                                <?php if ($board['use_category'] === 'Y'): ?>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?php echo xssFilter($post['category'] ?? '-'); ?></span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <a href="/bbs/<?php echo $board['board_id']; ?>/view/<?php echo $post['uid']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo xssFilter($post['subject']); ?>
                                        <?php if ($post['comments'] > 0): ?>
                                            <span class="badge bg-info ms-1"><?php echo $post['comments']; ?></span>
                                        <?php endif; ?>
                                        <?php if (strtotime($post['created_at']) > time() - 86400): ?>
                                            <span class="badge bg-danger ms-1">NEW</span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="text-center"><?php echo xssFilter($post['writer']); ?></td>
                                <td class="text-center"><?php echo number_format($post['views']); ?></td>
                                <td class="text-center"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo $board['use_category'] === 'Y' ? 6 : 5; ?>" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    등록된 게시물이 없습니다.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- 페이징 -->
            <?php if ($total_pages > 1): ?>
                <div class="card-body border-top">
                    <nav>
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $current_page - 5);
                            $end = min($total_pages, $current_page + 5);
                            
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?>">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    
                    <div class="text-center text-muted mt-3">
                        <small>
                            전체 <?php echo number_format($total); ?>개 중 
                            <?php echo number_format(($current_page - 1) * $board['posts_per_page'] + 1); ?> - 
                            <?php echo number_format(min($current_page * $board['posts_per_page'], $total)); ?>개 표시
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../_footer.php'; ?>
