<?php
/**
 * Board Model
 * 게시판 모델
 */

require_once __DIR__ . '/DBModel.php';

class BoardModel extends DBModel {
    
    protected $table = 'bbs_index';
    
    /**
     * 게시판 정보 조회
     */
    public function getBoardInfo($boardId) {
        return getUidData("SELECT * FROM bbs_list WHERE bbs_id = ?", [$boardId]);
    }
    
    /**
     * 게시물 목록 조회 (페이징)
     */
    public function getPostList($boardId, $page = 1, $perPage = 20, $category = null, $search = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["bbs_id = ?"];
        $params = [$boardId];
        
        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        
        if ($search) {
            $where[] = "(title LIKE ? OR content LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        // 전체 개수
        $total = getDbCnt("SELECT COUNT(*) FROM bbs_data WHERE {$whereClause}", $params);
        
        // 공지사항
        $notices = getDbArray("
            SELECT * FROM bbs_data 
            WHERE bbs_id = ? AND is_notice = 'Y'
            ORDER BY reg_date DESC
        ", [$boardId]);
        
        // 일반 게시물
        $posts = getDbArray("
            SELECT * FROM bbs_data 
            WHERE {$whereClause} AND is_notice = 'N'
            ORDER BY reg_date DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $params);
        
        return [
            'notices' => $notices,
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * 게시물 상세 조회
     */
    public function getPost($uid) {
        $post = getUidData("SELECT * FROM bbs_data WHERE uid = ?", [$uid]);
        
        if ($post) {
            // 조회수 증가
            getDbUpdate('bbs_data', ['view_count' => $post['view_count'] + 1], 'uid = ?', [$uid]);
            $post['view_count']++;
        }
        
        return $post;
    }
    
    /**
     * 게시물 작성
     */
    public function createPost($data) {
        $postData = [
            'bbs_id' => $data['bbs_id'],
            'category' => $data['category'] ?? null,
            'title' => $data['title'],
            'content' => $data['content'],
            'name' => $data['name'],
            'member_uid' => $data['member_uid'] ?? null,
            'password' => isset($data['password']) ? hashPassword($data['password']) : null,
            'is_notice' => $data['is_notice'] ?? 'N',
            'is_secret' => $data['is_secret'] ?? 'N',
            'ip_address' => getClientIP()
        ];
        
        $postId = getDbInsert('bbs_data', $postData);
        
        // 포인트 적립 (로그인 회원만)
        if ($postId && !empty($data['member_uid'])) {
            require_once __DIR__ . '/PointModel.php';
            $pointModel = new PointModel();
            $pointModel->rewardPost($data['member_uid'], $postId);
        }
        
        return $postId;
    }
    
    /**
     * 게시물 수정
     */
    public function updatePost($uid, $data) {
        $updateData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'category' => $data['category'] ?? null
        ];
        
        return getDbUpdate('bbs_data', $updateData, 'uid = ?', [$uid]);
    }
    
    /**
     * 게시물 삭제
     */
    public function deletePost($uid) {
        return getDbDelete('bbs_data', 'uid = ?', [$uid]);
    }
    
    /**
     * 댓글 목록 조회
     */
    public function getComments($postUid) {
        return getDbArray("
            SELECT * FROM bbs_comment 
            WHERE data_uid = ?
            ORDER BY reg_date ASC
        ", [$postUid]);
    }
    
    /**
     * 댓글 작성
     */
    public function createComment($data) {
        $commentData = [
            'bbs_id' => $data['bbs_id'] ?? 'default',
            'data_uid' => $data['data_uid'],
            'parent_uid' => $data['parent_uid'] ?? 0,
            'member_uid' => $data['member_uid'] ?? 0,
            'name' => $data['name'],
            'content' => $data['content'],
            'password' => isset($data['password']) ? hashPassword($data['password']) : null,
            'is_secret' => $data['is_secret'] ?? 'N',
            'ip_address' => $data['ip_address'] ?? getClientIP()
        ];
        
        $commentId = getDbInsert('bbs_comment', $commentData);
        
        if ($commentId) {
            // 게시물의 댓글 수 업데이트
            getDbUpdate('bbs_data', 
                ['comment_count' => getDbCnt("SELECT COUNT(*) FROM bbs_comment WHERE data_uid = ?", [$data['data_uid']])],
                'uid = ?',
                [$data['data_uid']]
            );
            
            // 포인트 적립 (로그인 회원만)
            if (!empty($data['member_uid'])) {
                require_once __DIR__ . '/PointModel.php';
                $pointModel = new PointModel();
                $pointModel->rewardComment($data['member_uid'], $commentId);
            }
        }
        
        return $commentId;
    }
    
    /**
     * 댓글 삭제
     */
    public function deleteComment($uid, $postUid) {
        // 물리적 삭제
        $result = getDbDelete('bbs_comment', 'uid = ?', [$uid]);
        
        if ($result) {
            // 게시물의 댓글 수 업데이트
            getDbUpdate('bbs_data', 
                ['comment_count' => getDbCnt("SELECT COUNT(*) FROM bbs_comment WHERE data_uid = ?", [$postUid])],
                'uid = ?',
                [$postUid]
            );
        }
        
        return $result;
    }
    
    /**
     * 이전/다음 게시물 조회
     */
    public function getPrevNext($boardId, $currentUid) {
        // 이전 글
        $prev = getUidData("
            SELECT uid, title FROM bbs_data 
            WHERE bbs_id = ? AND uid < ?
            ORDER BY uid DESC LIMIT 1
        ", [$boardId, $currentUid]);
        
        // 다음 글
        $next = getUidData("
            SELECT uid, title FROM bbs_data 
            WHERE bbs_id = ? AND uid > ?
            ORDER BY uid ASC LIMIT 1
        ", [$boardId, $currentUid]);
        
        return ['prev' => $prev, 'next' => $next];
    }
    
    /**
     * 파일 첨부 정보 저장
     */
    public function attachFiles($postUid, $files, $boardId = null) {
        if (empty($files)) return true;
        
        // 게시물 정보로 board_id 가져오기
        if (!$boardId) {
            $post = getUidData("SELECT bbs_id FROM bbs_data WHERE uid = ?", [$postUid]);
            $boardId = $post['bbs_id'] ?? '';
        }
        
        $insertedCount = 0;
        
        foreach ($files as $file) {
            if (isset($file['success']) && $file['success']) {
                $result = getDbInsert('bbs_upload', [
                    'post_uid' => $postUid,
                    'board_id' => $boardId,
                    'filename' => $file['filename'] ?? '',
                    'original_name' => $file['original_name'] ?? '',
                    'filepath' => $file['filepath'] ?? '',
                    'filesize' => $file['size'] ?? 0,
                    'mime_type' => $file['mime_type'] ?? ''
                ]);
                
                if ($result) {
                    $insertedCount++;
                }
            }
        }
        
        return $insertedCount > 0;
    }
    
    /**
     * 파일 삭제 (UID 배열로)
     */
    public function deleteFiles($postUid, $deleteFileUids) {
        if (empty($deleteFileUids)) return true;
        
        $deletedCount = 0;
        
        foreach ($deleteFileUids as $fileUid) {
            if (empty($fileUid) || $fileUid === '') continue;
            
            // 파일 정보 조회
            $file = getUidData("SELECT * FROM bbs_upload WHERE uid = ? AND post_uid = ?", [$fileUid, $postUid]);
            
            if ($file) {
                // 실제 파일 삭제
                $filePath = PUBLIC_PATH . '/uploads/' . $file['filepath'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
                
                // DB에서 삭제
                $result = getDbDelete('bbs_upload', 'uid = ?', [$fileUid]);
                if ($result) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount > 0;
    }
    
    /**
     * 게시물의 파일 목록 조회
     */
    public function getPostFiles($postUid) {
        return getDbArray("
            SELECT * FROM bbs_upload 
            WHERE post_uid = ? 
            ORDER BY uid ASC
        ", [$postUid]);
    }
    
    /**
     * 파일 다운로드 횟수 증가
     */
    public function incrementDownload($fileUid) {
        $file = getUidData("SELECT * FROM bbs_data WHERE uid = ?", [$fileUid]);
        if ($file) {
            getDbUpdate('bbs_data', ['download_count' => $file['download_count'] + 1], 'uid = ?', [$fileUid]);
        }
    }
}
