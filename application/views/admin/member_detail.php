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
            <div class="breadcrumb">
                <a href="/admin">대시보드</a> &gt; 
                <a href="/admin/members">회원 관리</a> &gt; 
                <span>회원 상세</span>
            </div>
        </header>
        
        <?php if (!empty($member)): ?>
        <div class="detail-container">
            <!-- 회원 정보 -->
            <div class="detail-section">
                <h2>기본 정보</h2>
                <form id="memberForm" method="POST" action="/admin/member/<?php echo $member['uid']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="form-group">
                        <label>UID</label>
                        <input type="text" value="<?php echo $member['uid']; ?>" readonly class="readonly">
                    </div>
                    
                    <div class="form-group">
                        <label>아이디</label>
                        <input type="text" value="<?php echo xssFilter($member['user_id']); ?>" readonly class="readonly">
                    </div>
                    
                    <div class="form-group">
                        <label>이름 *</label>
                        <input type="text" name="name" value="<?php echo xssFilter($member['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>이메일 *</label>
                        <input type="email" name="email" value="<?php echo xssFilter($member['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>등급 *</label>
                        <select name="level" required>
                            <option value="1" <?php echo $member['level'] == 1 ? 'selected' : ''; ?>>일반 회원 (1)</option>
                            <option value="5" <?php echo $member['level'] == 5 ? 'selected' : ''; ?>>정회원 (5)</option>
                            <option value="9" <?php echo $member['level'] == 9 ? 'selected' : ''; ?>>관리자 (9)</option>
                            <option value="10" <?php echo $member['level'] == 10 ? 'selected' : ''; ?>>최고관리자 (10)</option>
                        </select>
                        <small>등급이 9 이상이면 관리자 권한을 가집니다.</small>
                    </div>
                    
                    <div class="form-group">
                        <label>포인트</label>
                        <input type="number" name="point" value="<?php echo $member['point'] ?? 0; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>상태 *</label>
                        <select name="status" required>
                            <option value="active" <?php echo ($member['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>활성</option>
                            <option value="inactive" <?php echo ($member['status'] ?? 'active') == 'inactive' ? 'selected' : ''; ?>>비활성</option>
                            <option value="banned" <?php echo ($member['status'] ?? 'active') == 'banned' ? 'selected' : ''; ?>>차단</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>비밀번호 재설정</label>
                        <input type="password" name="new_password" placeholder="변경하려면 입력하세요">
                        <small>비어있으면 변경하지 않습니다.</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">저장</button>
                        <a href="/admin/members" class="btn-secondary">목록</a>
                        <button type="button" onclick="deleteMember()" class="btn-danger">회원 삭제</button>
                    </div>
                </form>
            </div>
            
            <!-- 활동 통계 -->
            <div class="detail-section">
                <h2>활동 통계</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">게시물</div>
                        <div class="stat-value"><?php echo number_format($member['post_count'] ?? 0); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">댓글</div>
                        <div class="stat-value"><?php echo number_format($member['comment_count'] ?? 0); ?></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">포인트</div>
                        <div class="stat-value"><?php echo number_format($member['point'] ?? 0); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- 활동 정보 -->
            <div class="detail-section">
                <h2>로그인 정보</h2>
                <table class="info-table">
                    <tr>
                        <th>가입일</th>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($member['reg_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>최근 로그인</th>
                        <td>
                            <?php 
                            echo $member['last_login'] 
                                ? date('Y-m-d H:i:s', strtotime($member['last_login'])) 
                                : '기록 없음'; 
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>상태</th>
                        <td>
                            <?php
                            $statusLabel = [
                                'active' => '✅ 활성',
                                'inactive' => '⏸ 비활성',
                                'banned' => '🚫 차단'
                            ];
                            echo $statusLabel[$member['status'] ?? 'active'];
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="error-message">
            <p>회원을 찾을 수 없습니다.</p>
            <a href="/admin/members" class="btn-primary">회원 목록으로</a>
        </div>
        <?php endif; ?>
    </main>
    
    <script>
    // 폼 제출
    document.getElementById('memberForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch(e.target.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(result.message || '저장되었습니다.');
                window.location.reload();
            } else {
                alert(result.message || '저장 실패');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        }
    });
    
    // 회원 삭제
    function deleteMember() {
        if (!confirm('정말 이 회원을 삭제하시겠습니까?\n\n이 작업은 되돌릴 수 없습니다.')) {
            return;
        }
        
        const uid = <?php echo $member['uid'] ?? 0; ?>;
        
        fetch(`/admin/member/${uid}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                csrf_token: '<?php echo generateCsrfToken(); ?>'
            })
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert('삭제되었습니다.');
                window.location.href = '/admin/members';
            } else {
                alert(result.message || '삭제 실패');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('오류가 발생했습니다.');
        });
    }
    </script>
</body>
</html>
