<?php
/**
 * News Model
 * 뉴스 모델 - BBS와 완전히 독립적인 뉴스 전용 시스템
 */

require_once __DIR__ . '/DBModel.php';

class NewsModel extends DBModel {
    
    protected $table = 'news_index';
    
    /**
     * 뉴스 게시판 정보 조회
     */
    public function getNewsInfo($newsId) {
        return getUidData("
            SELECT *,
                   news_id as news_id,
                   news_name as news_name,
                   news_skin as news_skin,
                   page_rows as posts_per_page,
                   use_upload as use_comment,
                   use_attach as use_category
            FROM news_list 
            WHERE news_id = ?
        ", [$newsId]);
    }
    
    /**
     * 뉴스 게시물 목록 조회 (페이징)
     */
    public function getPostList($newsId, $page = 1, $perPage = 20, $category = null, $search = null) {
        $offset = ($page - 1) * $perPage;
        
        // 🔍 검색어가 있으면 news_data 직접 검색
        if ($search) {
            $where = ["news_id = ?"];
            $params = [$newsId];
            
            if ($category) {
                $where[] = "category = ?";
                $params[] = $category;
            }
            
            $where[] = "(title LIKE ? OR content LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            
            $whereClause = implode(' AND ', $where);
            
            // 전체 개수
            $total = getDbCnt("SELECT COUNT(*) FROM news_data WHERE {$whereClause}", $params);
            
            // 공지사항
            $notices = getDbArray("
                SELECT * FROM news_data 
                WHERE news_id = ? AND is_notice = 'Y'
                ORDER BY reg_date DESC
            ", [$newsId]);
            
            // 일반 게시물
            $posts = getDbArray("
                SELECT * FROM news_data 
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
        
        // ✅ 일반 리스트: news_index 기반 조회 (성능 최적화)
        // 공지사항
        $notices = getDbArray("
            SELECT d.* 
            FROM news_index i
            INNER JOIN news_data d ON i.data_uid = d.uid
            WHERE i.news_id = ? AND i.is_notice = 'Y'
            ORDER BY d.reg_date DESC
        ", [$newsId]);
        
        // 전체 개수 (news_index 기반)
        $countWhere = ['i.news_id = ?', 'i.is_notice = ?'];
        $countParams = [$newsId, 'N'];
        
        if ($category) {
            $countWhere[] = 'i.category = ?';
            $countParams[] = $category;
        }
        
        $countWhereClause = implode(' AND ', $countWhere);
        $total = getDbCnt("SELECT COUNT(*) FROM news_index i WHERE {$countWhereClause}", $countParams);
        
        // 일반 게시물 (news_index + news_data 조인)
        $postWhere = ['i.news_id = ?', 'i.is_notice = ?'];
        $postParams = [$newsId, 'N'];
        
        if ($category) {
            $postWhere[] = 'i.category = ?';
            $postParams[] = $category;
        }
        
        $postWhereClause = implode(' AND ', $postWhere);
        $posts = getDbArray("
            SELECT d.* 
            FROM news_index i
            INNER JOIN news_data d ON i.data_uid = d.uid
            WHERE {$postWhereClause}
            ORDER BY d.reg_date DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", $postParams);
        
        return [
            'notices' => $notices,
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * 뉴스 게시물 상세 조회
     */
    public function getPost($uid) {
        $post = getUidData("SELECT * FROM news_data WHERE uid = ?", [$uid]);
        
        if ($post) {
            // 조회수 증가
            getDbUpdate('news_data', ['view_count' => $post['view_count'] + 1], 'uid = ?', [$uid]);
            $post['view_count']++;
            
            // 좋아요 수 가져오기
            $likeCount = getUidData("SELECT COUNT(*) as cnt FROM post_likes WHERE post_type = 'news' AND post_uid = ?", [$uid]);
            $post['like_count'] = $likeCount ? $likeCount['cnt'] : 0;
        }
        
        return $post;
    }
    
    /**
     * 뉴스 게시물 작성
     */
    public function createPost($data) {
        $postData = [
            'news_id' => $data['news_id'],
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
        
        $postId = getDbInsert('news_data', $postData);
        
        // 포인트 적립 (로그인 회원만)
        if ($postId && !empty($data['member_uid'])) {
            require_once __DIR__ . '/PointModel.php';
            $pointModel = new PointModel();
            $pointModel->rewardPost($data['member_uid'], $postId);
        }
        
        return $postId;
    }
    
    /**
     * 뉴스 게시물 수정
     */
    public function updatePost($uid, $data) {
        $updateData = [
            'title' => $data['title'],
            'content' => $data['content'],
            'category' => $data['category'] ?? null
        ];
        
        // 비밀글 업데이트
        if (isset($data['is_secret'])) {
            $updateData['is_secret'] = $data['is_secret'];
        }
        
        // 공지사항 업데이트 (관리자만)
        if (isset($data['is_notice'])) {
            $updateData['is_notice'] = $data['is_notice'];
        }
        
        return getDbUpdate('news_data', $updateData, 'uid = ?', [$uid]);
    }
    
    /**
     * 뉴스 게시물 삭제
     */
    public function deletePost($uid) {
        return getDbDelete('news_data', 'uid = ?', [$uid]);
    }
    
    /**
     * 뉴스 댓글 목록 조회
     */
    public function getComments($postUid) {
        return getDbArray("
            SELECT * FROM news_comment 
            WHERE data_uid = ?
            ORDER BY reg_date ASC
        ", [$postUid]);
    }
    
    /**
     * 뉴스 댓글 작성
     */
    public function createComment($data) {
        $commentData = [
            'news_id' => $data['news_id'] ?? 'default',
            'data_uid' => $data['data_uid'],
            'parent_uid' => $data['parent_uid'] ?? 0,
            'member_uid' => $data['member_uid'] ?? 0,
            'name' => $data['name'],
            'content' => $data['content'],
            'password' => isset($data['password']) ? hashPassword($data['password']) : null,
            'is_secret' => $data['is_secret'] ?? 'N',
            'ip_address' => $data['ip_address'] ?? getClientIP()
        ];
        
        $commentId = getDbInsert('news_comment', $commentData);
        
        if ($commentId) {
            // 게시물의 댓글 수 업데이트
            getDbUpdate('news_data', 
                ['comment_count' => getDbCnt("SELECT COUNT(*) FROM news_comment WHERE data_uid = ?", [$data['data_uid']])],
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
     * 뉴스 댓글 삭제
     */
    public function deleteComment($uid, $postUid) {
        // 물리적 삭제
        $result = getDbDelete('news_comment', 'uid = ?', [$uid]);
        
        if ($result) {
            // 게시물의 댓글 수 업데이트
            getDbUpdate('news_data', 
                ['comment_count' => getDbCnt("SELECT COUNT(*) FROM news_comment WHERE data_uid = ?", [$postUid])],
                'uid = ?',
                [$postUid]
            );
        }
        
        return $result;
    }
    
    /**
     * 이전/다음 뉴스 게시물 조회
     */
    public function getPrevNext($newsId, $currentUid) {
        // 이전 글
        $prev = getUidData("
            SELECT uid, title FROM news_data 
            WHERE news_id = ? AND uid < ?
            ORDER BY uid DESC LIMIT 1
        ", [$newsId, $currentUid]);
        
        // 다음 글
        $next = getUidData("
            SELECT uid, title FROM news_data 
            WHERE news_id = ? AND uid > ?
            ORDER BY uid ASC LIMIT 1
        ", [$newsId, $currentUid]);
        
        return ['prev' => $prev, 'next' => $next];
    }
    
    /**
     * 파일 첨부 정보 저장
     */
    public function attachFiles($postUid, $files, $newsId = null) {
        if (empty($files)) return true;
        
        // 게시물 정보로 news_id 가져오기
        if (!$newsId) {
            $post = getUidData("SELECT news_id FROM news_data WHERE uid = ?", [$postUid]);
            $newsId = $post['news_id'] ?? '';
        }
        
        $insertedCount = 0;
        
        foreach ($files as $file) {
            if (isset($file['success']) && $file['success']) {
                $result = getDbInsert('news_upload', [
                    'post_uid' => $postUid,
                    'news_id' => $newsId,
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
            $file = getUidData("SELECT * FROM news_upload WHERE uid = ? AND post_uid = ?", [$fileUid, $postUid]);
            
            if ($file) {
                // 실제 파일 삭제
                $filePath = PUBLIC_PATH . '/uploads/' . $file['filepath'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
                
                // DB에서 삭제
                $result = getDbDelete('news_upload', 'uid = ?', [$fileUid]);
                if ($result) {
                    $deletedCount++;
                }
            }
        }
        
        return $deletedCount > 0;
    }
    
    /**
     * 뉴스 게시물의 파일 목록 조회
     */
    public function getPostFiles($postUid) {
        return getDbArray("
            SELECT * FROM news_upload 
            WHERE post_uid = ? 
            ORDER BY uid ASC
        ", [$postUid]);
    }
    
    /**
     * 파일 다운로드 횟수 증가
     */
    public function incrementDownload($fileUid) {
        $file = getUidData("SELECT * FROM news_data WHERE uid = ?", [$fileUid]);
        if ($file) {
            getDbUpdate('news_data', ['download_count' => $file['download_count'] + 1], 'uid = ?', [$fileUid]);
        }
    }
}
