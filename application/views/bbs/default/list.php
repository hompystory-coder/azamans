<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/board.css">
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
                <?php if ($board['use_category'] === 'Y' && !empty($board['categories'])): ?>
                    <select name="category" class="search-select">
                        <option value="">전체 카테고리</option>
                        <?php 
                        $categories = json_decode($board['categories'], true);
                        foreach ($categories as $cat):
                        ?>
                            <option value="<?php echo $cat; ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                
                <input type="text" name="search" placeholder="제목 또는 내용 검색" 
                       value="<?php echo xssFilter($search ?? ''); ?>" class="search-input">
                <button type="submit" class="btn-search">검색</button>
            </form>
        </div>
        
        <!-- 게시물 목록 -->
        <div class="board-list">
            <table>
                <thead>
                    <tr>
                        <th width="80">번호</th>
                        <?php if ($board['use_category'] === 'Y'): ?>
                            <th width="100">카테고리</th>
                        <?php endif; ?>
                        <th>제목</th>
                        <th width="120">작성자</th>
                        <th width="80">조회</th>
                        <th width="120">작성일</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 공지사항 -->
                    <?php foreach ($notices as $notice): ?>
                    <tr class="notice-row">
                        <td><span class="badge-notice">공지</span></td>
                        <?php if ($board['use_category'] === 'Y'): ?>
                            <td><?php echo xssFilter($notice['category'] ?? '-'); ?></td>
                        <?php endif; ?>
                        <td class="subject">
                            <a href="/bbs/<?php echo $board['board_id']; ?>/view/<?php echo $notice['uid']; ?>">
                                <?php echo xssFilter($notice['subject']); ?>
                                <?php if ($notice['comments'] > 0): ?>
                                    <span class="comment-count">[<?php echo $notice['comments']; ?>]</span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td><?php echo xssFilter($notice['writer']); ?></td>
                        <td><?php echo number_format($notice['views']); ?></td>
                        <td><?php echo formatDate($notice['created_at'], 'Y-m-d'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- 일반 게시물 -->
                    <?php if (!empty($posts)): ?>
                        <?php 
                        $num = $total - (($current_page - 1) * $board['posts_per_page']);
                        foreach ($posts as $post): 
                        ?>
                        <tr>
                            <td><?php echo $num--; ?></td>
                            <?php if ($board['use_category'] === 'Y'): ?>
                                <td><?php echo xssFilter($post['category'] ?? '-'); ?></td>
                            <?php endif; ?>
                            <td class="subject">
                                <a href="/bbs/<?php echo $board['board_id']; ?>/view/<?php echo $post['uid']; ?>">
                                    <?php echo xssFilter($post['subject']); ?>
                                    <?php if ($post['comments'] > 0): ?>
                                        <span class="comment-count">[<?php echo $post['comments']; ?>]</span>
                                    <?php endif; ?>
                                    <?php if (strtotime($post['created_at']) > time() - 86400): ?>
                                        <span class="badge-new">NEW</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td><?php echo xssFilter($post['writer']); ?></td>
                            <td><?php echo number_format($post['views']); ?></td>
                            <td><?php echo formatDate($post['created_at'], 'Y-m-d'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?php echo $board['use_category'] === 'Y' ? 6 : 5; ?>" class="no-posts">
                                등록된 게시물이 없습니다.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1): ?>
            <div class="board-pagination">
                <?php 
                $params = [];
                if (!empty($category)) $params['category'] = $category;
                if (!empty($search)) $params['search'] = $search;
                echo renderPagination($current_page, $total_pages, "/bbs/{$board['board_id']}", $params); 
                ?>
            </div>
            <?php echo renderPaginationInfo($current_page, $total_pages, $total, $board['posts_per_page']); ?>
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
