<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xssFilter($title); ?> - 관리자</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="admin-main">
        <header class="admin-header">
            <h1><?php echo xssFilter($title); ?></h1>
            <button onclick="showCreateBoard()" class="btn-primary">새 게시판 생성</button>
        </header>
        
        <!-- 게시판 목록 -->
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>UID</th>
                        <th>게시판 ID</th>
                        <th>게시판 이름</th>
                        <th>스킨</th>
                        <th>게시물 수</th>
                        <th>읽기 권한</th>
                        <th>쓰기 권한</th>
                        <th>댓글</th>
                        <th>카테고리</th>
                        <th>상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($boards)): ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 40px;">
                            게시판이 없습니다.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($boards as $board): ?>
                        <tr>
                            <td><?php echo $board['uid']; ?></td>
                            <td><code><?php echo xssFilter($board['board_id']); ?></code></td>
                            <td><strong><?php echo xssFilter($board['board_name']); ?></strong></td>
                            <td><?php echo xssFilter($board['board_skin'] ?? 'default'); ?></td>
                            <td><?php echo number_format($board['post_count'] ?? 0); ?></td>
                            <td>Lv.<?php echo $board['read_level'] ?? 1; ?></td>
                            <td>Lv.<?php echo $board['write_level'] ?? 1; ?></td>
                            <td>
                                <?php echo ($board['use_comment'] ?? 'Y') === 'Y' ? '✅' : '❌'; ?>
                            </td>
                            <td>
                                <?php echo ($board['use_category'] ?? 'N') === 'Y' ? '✅' : '❌'; ?>
                            </td>
                            <td>
                                <?php
                                $status = $board['status'] ?? 'active';
                                $statusClass = $status === 'active' ? 'success' : 'danger';
                                $statusLabel = $status === 'active' ? '활성' : '비활성';
                                ?>
                                <span class="badge badge-<?php echo $statusClass; ?>">
                                    <?php echo $statusLabel; ?>
                                </span>
                            </td>
                            <td>
                                <a href="/bbs/<?php echo $board['board_id']; ?>" class="btn-sm btn-info" target="_blank">
                                    보기
                                </a>
                                <a href="/admin/board/<?php echo $board['uid']; ?>" class="btn-sm btn-warning">
                                    수정
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- 게시판 생성 모달 -->
    <div id="createBoardModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>새 게시판 생성</h2>
                <button onclick="closeCreateBoard()" class="close">&times;</button>
            </div>
            <form id="createBoardForm">
                <div class="form-group">
                    <label>게시판 ID * (영문, 숫자만 가능)</label>
                    <input type="text" name="board_id" required pattern="[a-z0-9_]+" 
                           placeholder="예: notice, free, qna">
                    <small>게시판 주소에 사용됩니다. (예: /bbs/notice)</small>
                </div>
                
                <div class="form-group">
                    <label>게시판 이름 *</label>
                    <input type="text" name="board_name" required placeholder="예: 공지사항, 자유게시판">
                </div>
                
                <div class="form-group">
                    <label>스킨</label>
                    <select name="board_skin">
                        <option value="default">기본형</option>
                        <option value="gallery">갤러리형</option>
                        <option value="blog">블로그형</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>페이지당 게시물 수</label>
                    <input type="number" name="posts_per_page" value="20" min="10" max="100">
                </div>
                
                <div class="form-group">
                    <label>읽기 권한</label>
                    <select name="read_level">
                        <option value="1">일반 (1)</option>
                        <option value="5">정회원 (5)</option>
                        <option value="9">관리자 (9)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>쓰기 권한</label>
                    <select name="write_level">
                        <option value="1">일반 (1)</option>
                        <option value="5">정회원 (5)</option>
                        <option value="9">관리자 (9)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>댓글 권한</label>
                    <select name="comment_level">
                        <option value="1">일반 (1)</option>
                        <option value="5">정회원 (5)</option>
                        <option value="9">관리자 (9)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="use_comment" value="Y" checked>
                        댓글 사용
                    </label>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="use_category" value="Y">
                        카테고리 사용
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">생성</button>
                    <button type="button" onclick="closeCreateBoard()" class="btn-secondary">취소</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function showCreateBoard() {
        document.getElementById('createBoardModal').style.display = 'flex';
    }
    
    function closeCreateBoard() {
        document.getElementById('createBoardModal').style.display = 'none';
        document.getElementById('createBoardForm').reset();
    }
    
    document.getElementById('createBoardForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key === 'use_comment' || key === 'use_category') {
                data[key] = value;
            } else {
                data[key] = value;
            }
        }
        
        // 체크박스가 체크되지 않은 경우 N으로 설정
        if (!data.use_comment) data.use_comment = 'N';
        if (!data.use_category) data.use_category = 'N';
        
        try {
            const response = await fetch('/admin/boards', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('게시판이 생성되었습니다.');
                window.location.reload();
            } else {
                alert(result.message || '생성 실패');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        }
    });
    
    // 모달 외부 클릭 시 닫기
    window.onclick = function(event) {
        const modal = document.getElementById('createBoardModal');
        if (event.target === modal) {
            closeCreateBoard();
        }
    }
    </script>
    
    <style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background-color: white;
        padding: 0;
        border-radius: 8px;
        width: 90%;
        max-width: 600px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #ddd;
    }
    
    .modal-header h2 {
        margin: 0;
    }
    
    .close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #aaa;
    }
    
    .close:hover {
        color: #000;
    }
    
    .modal-content form {
        padding: 20px;
    }
    </style>
</body>
</html>
