<?php include __DIR__ . '/../../_header.php'; ?>

<main>
    <div class="container">
        <!-- 게시물 헤더 -->
        <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="flex-grow-1">
                        <h2 class="fw-bold mb-2">
                            <?php if ($post['is_notice'] === 'Y'): ?>
                                <span class="badge bg-warning text-dark me-2">
                                    <i class="fas fa-bullhorn"></i> 공지
                                </span>
                            <?php endif; ?>
                            <?php echo xssFilter($post['title']); ?>
                        </h2>
                        <div class="text-muted">
                            <i class="fas fa-user me-1"></i><?php echo xssFilter($post['name']); ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-calendar me-1"></i><?php echo date('Y-m-d H:i', strtotime($post['reg_date'])); ?>
                            <span class="mx-2">|</span>
                            <i class="fas fa-eye me-1"></i><?php echo number_format($post['view_count']); ?>
                            <?php if ($post['comment_count'] > 0): ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-comments me-1"></i><?php echo $post['comment_count']; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 동영상 플레이어 -->
        <?php
        // 유튜브 링크에서 비디오 ID 추출
        $videoId = null;
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $post['content'], $match)) {
            $videoId = $match[1];
        }
        ?>
        
        <?php if ($videoId): ?>
            <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeIn">
                <div class="ratio ratio-16x9">
                    <iframe 
                        src="https://www.youtube.com/embed/<?php echo $videoId; ?>?rel=0&showinfo=0" 
                        title="<?php echo xssFilter($post['title']); ?>"
                        frameborder="0" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- 게시물 내용 -->
        <div class="card border-0 shadow-sm mb-4 animate__animated animate__fadeInUp">
            <div class="card-body">
                <div class="post-content" style="min-height: 100px;">
                    <?php 
                    // 유튜브 링크를 제외한 내용 표시
                    $content = $post['content'];
                    $content = preg_replace('/https?:\/\/(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', '', $content);
                    echo nl2br(xssFilter(trim($content))); 
                    ?>
                </div>
                
                <!-- 액션 버튼 -->
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <div>
                        <a href="/bbs/<?php echo $board['bbs_id']; ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>목록
                        </a>
                        <?php if (isLoggedIn() && ((isset($_SESSION['user_id']) && $post['member_uid'] == $_SESSION['user_id']) || isAdmin())): ?>
                            <a href="/bbs/<?php echo $board['bbs_id']; ?>/edit/<?php echo $post['uid']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>수정
                            </a>
                            <button onclick="deletePost('<?php echo $board['bbs_id']; ?>', <?php echo $post['uid']; ?>)" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-1"></i>삭제
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 좋아요 버튼 -->
                    <?php include __DIR__ . '/../_includes/like_button.php'; ?>
                </div>
            </div>
        </div>
        
        <!-- 이전/다음 글 -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        <i class="fas fa-angle-up me-2"></i>이전 글
                    </span>
                    <?php if ($prev): ?>
                        <a href="/bbs/<?php echo $board['bbs_id']; ?>/view/<?php echo $prev['uid']; ?>" 
                           class="text-decoration-none text-dark flex-grow-1 ms-3 text-truncate">
                            <?php echo xssFilter($prev['title']); ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted flex-grow-1 ms-3">이전 글이 없습니다.</span>
                    <?php endif; ?>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span class="text-muted">
                        <i class="fas fa-angle-down me-2"></i>다음 글
                    </span>
                    <?php if ($next): ?>
                        <a href="/bbs/<?php echo $board['bbs_id']; ?>/view/<?php echo $next['uid']; ?>" 
                           class="text-decoration-none text-dark flex-grow-1 ms-3 text-truncate">
                            <?php echo xssFilter($next['title']); ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted flex-grow-1 ms-3">다음 글이 없습니다.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php include __DIR__ . '/../_comment_section.php'; ?>
    </div>
</main>

<!-- 공통 CSS/JS -->
<link rel="stylesheet" href="/public/css/bbs_common.css">
<script src="/public/js/bbs_common.js"></script>

<?php include __DIR__ . '/../../_footer.php'; ?>
