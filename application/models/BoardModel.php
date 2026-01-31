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
        return getUidData("SELECT * FROM bbs_list WHERE board_id = ? AND status = 'active'", [$boardId]);
    }
    
    /**
     * 게시물 목록 조회 (페이징)
     */
    public function getPostList($boardId, $page = 1, $perPage = 20, $category = null, $search = null) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["board_id = ?", "status = 'active'"];
        $params = [$boardId];
        
        if ($category) {
            $where[] = "category = ?";
            $params[] = $category;
        }
        
        if ($search) {
            $where[] = "(subject LIKE ? OR content LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $whereClause = implode(' AND ', $where);
        
        // 전체 개수
        $total = getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE {$whereClause}", $params);
        
        // 공지사항
        $notices = getDbArray("
            SELECT * FROM bbs_index 
            WHERE board_id = ? AND status = 'active' AND is_notice = 'Y'
            ORDER BY created_at DESC
        ", [$boardId]);
        
        // 일반 게시물
        $posts = getDbArray("
            SELECT * FROM bbs_index 
            WHERE {$whereClause} AND is_notice = 'N'
            ORDER BY created_at DESC
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
        $post = getUidData("SELECT * FROM bbs_index WHERE uid = ? AND status = 'active'", [$uid]);
        
        if ($post) {
            // 조회수 증가
            getDbUpdate('bbs_index', ['views' => $post['views'] + 1], 'uid = ?', [$uid]);
            $post['views']++;
        }
        
        return $post;
    }
    
    /**
     * 게시물 작성
     */
    public function createPost($data) {
        $postData = [
            'board_id' => $data['board_id'],
            'category' => $data['category'] ?? null,
            'subject' => $data['subject'],
            'content' => $data['content'],
            'writer' => $data['writer'],
            'writer_uid' => $data['writer_uid'] ?? null,
            'password' => isset($data['password']) ? hashPassword($data['password']) : null,
            'is_notice' => $data['is_notice'] ?? 'N',
            'is_secret' => $data['is_secret'] ?? 'N',
            'ip_address' => getClientIP(),
            'status' => 'active'
        ];
        
        $postId = getDbInsert('bbs_index', $postData);
        
        // 포인트 적립 (로그인 회원만)
        if ($postId && !empty($data['writer_uid'])) {
            require_once __DIR__ . '/PointModel.php';
            $pointModel = new PointModel();
            $pointModel->rewardPost($data['writer_uid'], $postId);
        }
        
        return $postId;
    }
    
    /**
     * 게시물 수정
     */
    public function updatePost($uid, $data) {
        $updateData = [
            'subject' => $data['subject'],
            'content' => $data['content'],
            'category' => $data['category'] ?? null
        ];
        
        return getDbUpdate('bbs_index', $updateData, 'uid = ?', [$uid]);
    }
    
    /**
     * 게시물 삭제
     */
    public function deletePost($uid) {
        return getDbUpdate('bbs_index', ['status' => 'deleted'], 'uid = ?', [$uid]);
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
            'bbs_id' => $data['board_id'] ?? 'default',
            'data_uid' => $data['post_uid'],
            'parent_uid' => $data['parent_uid'] ?? 0,
            'member_uid' => $data['writer_uid'] ?? 0,
            'name' => $data['writer'],
            'content' => $data['content'],
            'password' => isset($data['password']) ? hashPassword($data['password']) : null,
            'is_secret' => $data['is_secret'] ?? 'N',
            'ip_address' => getClientIP()
        ];
        
        $commentId = getDbInsert('bbs_comment', $commentData);
        
        if ($commentId) {
            // 게시물의 댓글 수 업데이트
            getDbUpdate('bbs_index', 
                ['comments' => getDbCnt("SELECT COUNT(*) FROM bbs_comment WHERE data_uid = ?", [$data['post_uid']])],
                'uid = ?',
                [$data['post_uid']]
            );
            
            // 포인트 적립 (로그인 회원만)
            if (!empty($data['writer_uid'])) {
                require_once __DIR__ . '/PointModel.php';
                $pointModel = new PointModel();
                $pointModel->rewardComment($data['writer_uid'], $commentId);
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
            getDbUpdate('bbs_index', 
                ['comments' => getDbCnt("SELECT COUNT(*) FROM bbs_comment WHERE data_uid = ?", [$postUid])],
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
            SELECT uid, subject FROM bbs_index 
            WHERE board_id = ? AND status = 'active' AND uid < ?
            ORDER BY uid DESC LIMIT 1
        ", [$boardId, $currentUid]);
        
        // 다음 글
        $next = getUidData("
            SELECT uid, subject FROM bbs_index 
            WHERE board_id = ? AND status = 'active' AND uid > ?
            ORDER BY uid ASC LIMIT 1
        ", [$boardId, $currentUid]);
        
        return ['prev' => $prev, 'next' => $next];
    }
    
    /**
     * 파일 첨부 정보 저장
     */
    public function attachFiles($postUid, $files) {
        if (empty($files)) return true;
        
        $insertData = [];
        foreach ($files as $file) {
            $insertData[] = [
                'post_uid' => $postUid,
                'file_name' => $file['name'],
                'file_path' => $file['path'],
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'download_count' => 0
            ];
        }
        
        if (empty($insertData)) return true;
        
        // bbs_data 테이블에 파일 정보 삽입
        foreach ($insertData as $data) {
            getDbInsert('bbs_data', $data);
        }
        
        return true;
    }
    
    /**
     * 게시물의 파일 목록 조회
     */
    public function getPostFiles($postUid) {
        return getDbArray("
            SELECT * FROM bbs_data 
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
