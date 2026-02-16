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
                            <?php echo xssFilter($news['news_name']); ?>
                        </h2>
                        <p class="text-muted mb-0">
                            <i class="fas fa-file-alt me-1"></i>
                            전체 게시물: <strong><?php echo number_format($total); ?></strong>개
                        </p>
                    </div>
                    <?php if ($news['write_level'] <= ($_SESSION['level'] ?? 0)): ?>
                        <a href="/news/<?php echo $news['news_id']; ?>/write" class="btn btn-primary">
                            <i class="fas fa-pen me-2"></i>글쓰기
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 검색 폼 -->
        <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
            <div class="card-body">
                <form method="get" action="/news/<?php echo $news['news_id']; ?>" class="row g-3">
                    <?php if (!empty($news['news_category'])): ?>
                        <div class="col-md-3">
                            <select name="category" class="form-select">
                                <option value="">전체 카테고리</option>
                                <?php 
                                $categories = json_decode($news['news_category'], true);
                                if (is_array($categories)) {
                                    foreach ($categories as $cat):
                                ?>
                                    <option value="<?php echo xssFilter($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                                        <?php echo xssFilter($cat); ?>
                                    </option>
                                <?php 
                                    endforeach;
                                }
                                ?>
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
                            <?php if (!empty($news['news_category'])): ?>
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
                                <?php if (!empty($news['news_category'])): ?>
                                    <td class="text-center">
                                        <span class="badge bg-danger"><?php echo xssFilter($notice['category'] ?? '-'); ?></span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <a href="/news/<?php echo $news['news_id']; ?>/view/<?php echo $notice['uid']; ?>" 
                                       class="text-decoration-none text-dark fw-bold">
                                        <i class="fas fa-bullhorn text-danger me-1"></i>
                                        <?php echo xssFilter($notice['title']); ?>
                                        <?php if ($notice['comment_count'] > 0): ?>
                                            <span class="badge bg-primary ms-1"><?php echo $notice['comment_count']; ?></span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="text-center"><?php echo xssFilter($notice['name']); ?></td>
                                <td class="text-center"><?php echo number_format($notice['view_count']); ?></td>
                                <td class="text-center"><?php echo date('Y-m-d', strtotime($notice['reg_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <!-- 일반 게시물 -->
                        <?php if (!empty($posts)): ?>
                            <?php 
                            $num = $total - (($current_page - 1) * ($news['page_rows'] ?? 20));
                            foreach ($posts as $post): 
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $num--; ?></td>
                                <?php if (!empty($news['news_category'])): ?>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?php echo xssFilter($post['category'] ?? '-'); ?></span>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <a href="/news/<?php echo $news['news_id']; ?>/view/<?php echo $post['uid']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo xssFilter($post['title']); ?>
                                        <?php if ($post['comment_count'] > 0): ?>
                                            <span class="badge bg-info ms-1"><?php echo $post['comment_count']; ?></span>
                                        <?php endif; ?>
                                        <?php if (strtotime($post['reg_date']) > time() - 86400): ?>
                                            <span class="badge bg-danger ms-1">NEW</span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="text-center"><?php echo xssFilter($post['name']); ?></td>
                                <td class="text-center"><?php echo number_format($post['view_count']); ?></td>
                                <td class="text-center"><?php echo date('Y-m-d', strtotime($post['reg_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="<?php echo !empty($news['news_category']) ? 6 : 5; ?>" class="text-center py-5 text-muted">
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
                            <?php echo number_format(($current_page - 1) * $news['page_rows'] + 1); ?> - 
                            <?php echo number_format(min($current_page * $news['page_rows'], $total)); ?>개 표시
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../../_footer.php'; ?>
