<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/board.css">
    <link rel="stylesheet" href="/public/css/gallery.css">
</head>
<body>
    <?php include __DIR__ . '/../../_header.php'; ?>
    
    <main class="container board-container">
        <div class="board-header">
            <h1><?php echo xssFilter($board['board_name']); ?></h1>
            <p class="board-desc">전체 게시물: <?php echo number_format($total); ?>개</p>
        </div>
        
        <!-- 검색 폼 -->
        <div class="board-search">
            <form method="get" action="/bbs/<?php echo $board['board_id']; ?>">
                <input type="text" name="search" placeholder="제목 검색" 
                       value="<?php echo xssFilter($search ?? ''); ?>" class="search-input">
                <button type="submit" class="btn-search">검색</button>
            </form>
        </div>
        
        <!-- 갤러리 그리드 -->
        <div class="gallery-grid">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                <div class="gallery-item">
                    <a href="/bbs/<?php echo $board['board_id']; ?>/view/<?php echo $post['uid']; ?>" class="gallery-link">
                        <div class="gallery-image">
                            <?php 
                            // 첨부 이미지가 있으면 표시
                            $hasImage = false;
                            if (!empty($post['attachments'])) {
                                $attachments = json_decode($post['attachments'], true);
                                if (is_array($attachments) && !empty($attachments)) {
                                    $firstImage = array_values(array_filter($attachments, function($file) {
                                        return strpos($file['mime_type'] ?? '', 'image/') === 0;
                                    }))[0] ?? null;
                                    
                                    if ($firstImage) {
                                        $hasImage = true;
                                        echo '<img src="' . xssFilter($firstImage['url']) . '" alt="' . xssFilter($post['subject']) . '">';
                                    }
                                }
                            }
                            
                            if (!$hasImage) {
                                echo '<div class="no-image">📷<br>이미지 없음</div>';
                            }
                            ?>
                            
                            <div class="gallery-overlay">
                                <div class="gallery-info">
                                    <span class="gallery-views">👁️ <?php echo number_format($post['views']); ?></span>
                                    <?php if ($post['comments'] > 0): ?>
                                        <span class="gallery-comments">💬 <?php echo $post['comments']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="gallery-content">
                            <h3 class="gallery-title"><?php echo cutString(xssFilter($post['subject']), 30); ?></h3>
                            <div class="gallery-meta">
                                <span class="gallery-author"><?php echo xssFilter($post['writer']); ?></span>
                                <span class="gallery-date"><?php echo formatDate($post['created_at'], 'Y-m-d'); ?></span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-gallery-items">
                    <p>등록된 게시물이 없습니다.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
            <div class="board-pagination">
                <?php echo pagination($current_page, $total_pages, "/bbs/{$board['board_id']}/"); ?>
            </div>
        <?php endif; ?>
        
        <!-- 하단 버튼 -->
        <div class="board-footer">
            <a href="/" class="btn">목록으로</a>
            <?php if ($board['write_level'] <= ($_SESSION['level'] ?? 0)): ?>
                <a href="/bbs/<?php echo $board['board_id']; ?>/write" class="btn btn-primary">글쓰기</a>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include __DIR__ . '/../../_footer.php'; ?>
</body>
</html>
