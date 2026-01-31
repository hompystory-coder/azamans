<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - 관리자</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body class="admin-body">
    
    <!-- 관리자 사이드바 -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h2>관리자</h2>
            <p><?php echo $_SESSION['username']; ?></p>
        </div>
        <nav class="sidebar-nav">
            <a href="/admin" class="active">📊 대시보드</a>
            <a href="/admin/config">⚙️ 사이트 설정</a>
            <a href="/admin/members">👥 회원 관리</a>
            <a href="/admin/boards">📝 게시판 관리</a>
            <a href="/">🏠 사이트로 돌아가기</a>
            <a href="/member/logout">🚪 로그아웃</a>
        </nav>
    </aside>
    
    <!-- 관리자 메인 컨텐츠 -->
    <main class="admin-main">
        <div class="admin-header">
            <h1><?php echo $title; ?></h1>
            <p class="current-time"><?php echo date('Y년 m월 d일 H:i'); ?></p>
        </div>
        
        <!-- 통계 카드 -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>전체 회원</h3>
                    <p class="stat-number"><?php echo number_format($stats['total_members']); ?></p>
                    <span class="stat-sub">오늘: +<?php echo $stats['today_members']; ?></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-content">
                    <h3>전체 게시물</h3>
                    <p class="stat-number"><?php echo number_format($stats['total_posts']); ?></p>
                    <span class="stat-sub">오늘: +<?php echo $stats['today_posts']; ?></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💬</div>
                <div class="stat-content">
                    <h3>전체 댓글</h3>
                    <p class="stat-number"><?php echo number_format($stats['total_comments']); ?></p>
                    <span class="stat-sub">오늘: +<?php echo $stats['today_comments']; ?></span>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-content">
                    <h3>시스템 상태</h3>
                    <p class="stat-number">정상</p>
                    <span class="stat-sub">서버: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                </div>
            </div>
        </div>
        
        <div class="admin-grid">
            <!-- 최근 회원 -->
            <div class="admin-panel">
                <div class="panel-header">
                    <h2>최근 가입 회원</h2>
                    <a href="/admin/members" class="btn-small">전체보기</a>
                </div>
                <div class="panel-body">
                    <?php if (!empty($recent_members)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>아이디</th>
                                    <th>이름</th>
                                    <th>레벨</th>
                                    <th>가입일</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_members as $member): ?>
                                <tr>
                                    <td>
                                        <a href="/admin/member/<?php echo $member['uid']; ?>">
                                            <?php echo xssFilter($member['user_id']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo xssFilter($member['name']); ?></td>
                                    <td><span class="badge">Lv.<?php echo $member['level']; ?></span></td>
                                    <td><?php echo timeAgo($member['reg_date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">가입한 회원이 없습니다.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- 최근 게시물 -->
            <div class="admin-panel">
                <div class="panel-header">
                    <h2>최근 게시물</h2>
                    <a href="/admin/posts" class="btn-small">전체보기</a>
                </div>
                <div class="panel-body">
                    <?php if (!empty($recent_posts)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>게시판</th>
                                    <th>제목</th>
                                    <th>작성자</th>
                                    <th>조회</th>
                                    <th>작성일</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_posts as $post): ?>
                                <tr>
                                    <td><span class="badge-board"><?php echo xssFilter($post['board_id']); ?></span></td>
                                    <td>
                                        <a href="/bbs/<?php echo $post['board_id']; ?>/view/<?php echo $post['uid']; ?>">
                                            <?php echo cutString(xssFilter($post['subject']), 30); ?>
                                        </a>
                                    </td>
                                    <td><?php echo xssFilter($post['writer']); ?></td>
                                    <td><?php echo $post['views']; ?></td>
                                    <td><?php echo timeAgo($post['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">작성된 게시물이 없습니다.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- 시스템 정보 -->
        <div class="admin-panel">
            <div class="panel-header">
                <h2>시스템 정보</h2>
            </div>
            <div class="panel-body">
                <div class="info-grid">
                    <div class="info-item">
                        <strong>PHP 버전:</strong>
                        <span><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>서버 OS:</strong>
                        <span><?php echo PHP_OS; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>웹서버:</strong>
                        <span><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></span>
                    </div>
                    <div class="info-item">
                        <strong>DB 연결:</strong>
                        <span class="status-ok">✓ 정상</span>
                    </div>
                    <div class="info-item">
                        <strong>현재 시간:</strong>
                        <span><?php echo date('Y-m-d H:i:s'); ?></span>
                    </div>
                    <div class="info-item">
                        <strong>타임존:</strong>
                        <span><?php echo date_default_timezone_get(); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
</body>
</html>
