<?php
/**
 * 게시판 최적화 헬퍼 함수
 * - bbs_day, bbs_month, bbs_index 테이블 관리
 */

/**
 * 게시물 등록 시 최적화 테이블에 데이터 추가
 */
function newsInsertOptimizationData($newsId, $dataUid, $memberUid = null, $category = null, $isNotice = 'N', $isSecret = 'N') {
    try {
        $date = date('Ymd');
        $month = date('Ym');
        
        // 1. bbs_day 추가
        $dayData = [
            'news_id' => $newsId,
            'data_uid' => $dataUid,
            'member_uid' => $memberUid,
            'date' => $date
        ];
        getDbInsert('news_day', $dayData);
        
        // 2. bbs_month 추가
        $monthData = [
            'news_id' => $newsId,
            'data_uid' => $dataUid,
            'member_uid' => $memberUid,
            'date' => $month
        ];
        getDbInsert('news_month', $monthData);
        
        // 3. bbs_index 추가
        $indexData = [
            'news_id' => $newsId,
            'data_uid' => $dataUid,
            'member_uid' => $memberUid,
            'category' => $category,
            'is_notice' => $isNotice,
            'is_secret' => $isSecret
        ];
        getDbInsert('news_index', $indexData);
        
        return true;
    } catch (Exception $e) {
        error_log('newsInsertOptimizationData Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * 게시물 수정 시 최적화 테이블 업데이트
 */
function newsUpdateOptimizationData($newsId, $dataUid, $category = null, $isNotice = 'N', $isSecret = 'N') {
    try {
        // bbs_index만 업데이트 (날짜 정보는 변경 안 함)
        $updateData = [
            'category' => $category,
            'is_notice' => $isNotice,
            'is_secret' => $isSecret
        ];
        
        getDbUpdate('news_index', $updateData, 'bbs_id = ? AND data_uid = ?', [$newsId, $dataUid]);
        
        return true;
    } catch (Exception $e) {
        error_log('newsUpdateOptimizationData Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * 게시물 삭제 시 최적화 테이블에서 제거
 */
function newsDeleteOptimizationData($newsId, $dataUid) {
    try {
        // 1. bbs_day 삭제
        getDbDelete('news_day', 'bbs_id = ? AND data_uid = ?', [$newsId, $dataUid]);
        
        // 2. bbs_month 삭제
        getDbDelete('news_month', 'bbs_id = ? AND data_uid = ?', [$newsId, $dataUid]);
        
        // 3. bbs_index 삭제
        getDbDelete('news_index', 'bbs_id = ? AND data_uid = ?', [$newsId, $dataUid]);
        
        return true;
    } catch (Exception $e) {
        error_log('newsDeleteOptimizationData Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * bbs_index 기반 리스트 조회 (일반 리스트)
 */
function newsGetListFromIndex($newsId, $page = 1, $perPage = 20, $category = null, $isNoticeOnly = false) {
    $offset = ($page - 1) * $perPage;
    
    // WHERE 조건 생성
    $where = ['i.bbs_id = ?'];
    $params = [$newsId];
    
    if ($category) {
        $where[] = 'i.category = ?';
        $params[] = $category;
    }
    
    if ($isNoticeOnly) {
        $where[] = 'i.is_notice = ?';
        $params[] = 'Y';
    }
    
    $whereClause = implode(' AND ', $where);
    
    // bbs_index에서 페이징하고 bbs_data 조인
    $sql = "
        SELECT d.*
        FROM bbs_index i
        INNER JOIN bbs_data d ON i.data_uid = d.uid
        WHERE {$whereClause}
        ORDER BY i.is_notice DESC, i.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $perPage;
    $params[] = $offset;
    
    return getDbArray($sql, $params);
}

/**
 * bbs_index 기반 총 개수 조회
 */
function newsGetCountFromIndex($newsId, $category = null, $isNoticeOnly = false) {
    $where = ['bbs_id = ?'];
    $params = [$newsId];
    
    if ($category) {
        $where[] = 'category = ?';
        $params[] = $category;
    }
    
    if ($isNoticeOnly) {
        $where[] = 'is_notice = ?';
        $params[] = 'Y';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) as cnt FROM bbs_index WHERE {$whereClause}";
    $result = getUidData($sql, $params);
    
    return $result['cnt'] ?? 0;
}

/**
 * bbs_data 직접 검색 (검색어가 있을 때만)
 */
function newsSearchFromData($newsId, $searchField, $searchKeyword, $page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    
    $where = ['bbs_id = ?'];
    $params = [$newsId];
    
    if ($searchKeyword) {
        if ($searchField === 'all') {
            $where[] = '(title LIKE ? OR content LIKE ? OR writer_name LIKE ?)';
            $keyword = '%' . $searchKeyword . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        } else {
            $where[] = "{$searchField} LIKE ?";
            $params[] = '%' . $searchKeyword . '%';
        }
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT *
        FROM bbs_data
        WHERE {$whereClause}
        ORDER BY is_notice DESC, created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $perPage;
    $params[] = $offset;
    
    return getDbArray($sql, $params);
}

/**
 * 회원별 월별 게시물 수 조회
 */
function newsGetMemberMonthlyCount($memberUid, $newsId = null, $yearMonth = null) {
    $where = ['member_uid = ?'];
    $params = [$memberUid];
    
    if ($newsId) {
        $where[] = 'bbs_id = ?';
        $params[] = $newsId;
    }
    
    if ($yearMonth) {
        $where[] = 'date = ?';
        $params[] = $yearMonth;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) as cnt FROM bbs_month WHERE {$whereClause}";
    $result = getUidData($sql, $params);
    
    return $result['cnt'] ?? 0;
}

/**
 * 회원별 일별 게시물 수 조회
 */
function newsGetMemberDailyCount($memberUid, $newsId = null, $date = null) {
    $where = ['member_uid = ?'];
    $params = [$memberUid];
    
    if ($newsId) {
        $where[] = 'bbs_id = ?';
        $params[] = $newsId;
    }
    
    if ($date) {
        $where[] = 'date = ?';
        $params[] = $date;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) as cnt FROM bbs_day WHERE {$whereClause}";
    $result = getUidData($sql, $params);
    
    return $result['cnt'] ?? 0;
}

/**
 * 회원별 일별 게시물 수 조회
 * 
 * @param int $memberUid 회원 UID
 * @param string $date 날짜 (Ymd) - 기본값: 오늘
 * @param string|null $newsId 게시판 ID (선택)
 * @return int
 */
function newsGetMemberDayPostCount($memberUid, $date = null, $newsId = null) {
    if ($date === null) {
        $date = date('Ymd');
    }
    
    $where = "member_uid = ? AND date = ?";
    $params = [$memberUid, $date];
    
    if ($newsId !== null) {
        $where .= " AND bbs_id = ?";
        $params[] = $newsId;
    }
    
    return getDbCnt("SELECT COUNT(*) FROM bbs_day WHERE {$where}", $params);
}

/**
 * 회원별 월별 게시물 수 조회
 * 
 * @param int $memberUid 회원 UID
 * @param string $month 월 (Ym) - 기본값: 이번 달
 * @param string|null $newsId 게시판 ID (선택)
 * @return int
 */
function newsGetMemberMonthPostCount($memberUid, $month = null, $newsId = null) {
    if ($month === null) {
        $month = date('Ym');
    }
    
    $where = "member_uid = ? AND date = ?";
    $params = [$memberUid, $month];
    
    if ($newsId !== null) {
        $where .= " AND bbs_id = ?";
        $params[] = $newsId;
    }
    
    return getDbCnt("SELECT COUNT(*) FROM bbs_month WHERE {$where}", $params);
}

/**
 * 회원별 전체 게시물 수 조회 (bbs_index 사용)
 * 
 * @param int $memberUid 회원 UID
 * @param string|null $newsId 게시판 ID (선택)
 * @return int
 */
function newsGetMemberTotalPostCount($memberUid, $newsId = null) {
    $where = "member_uid = ?";
    $params = [$memberUid];
    
    if ($newsId !== null) {
        $where .= " AND bbs_id = ?";
        $params[] = $newsId;
    }
    
    return getDbCnt("SELECT COUNT(*) FROM bbs_index WHERE {$where}", $params);
}

/**
 * 게시판별 일별 게시물 수 조회
 * 
 * @param string $newsId 게시판 ID
 * @param string $date 날짜 (Ymd) - 기본값: 오늘
 * @return int
 */
function newsGetBoardDayPostCount($newsId, $date = null) {
    if ($date === null) {
        $date = date('Ymd');
    }
    
    return getDbCnt("SELECT COUNT(*) FROM bbs_day WHERE bbs_id = ? AND date = ?", [$newsId, $date]);
}

/**
 * 게시판별 월별 게시물 수 조회
 * 
 * @param string $newsId 게시판 ID
 * @param string $month 월 (Ym) - 기본값: 이번 달
 * @return int
 */
function newsGetBoardMonthPostCount($newsId, $month = null) {
    if ($month === null) {
        $month = date('Ym');
    }
    
    return getDbCnt("SELECT COUNT(*) FROM bbs_month WHERE bbs_id = ? AND date = ?", [$newsId, $month]);
}

/**
 * 회원별 일별 게시물 목록 조회
 * 
 * @param int $memberUid 회원 UID
 * @param string $date 날짜 (Ymd)
 * @param int $page 페이지 번호
 * @param int $perPage 페이지당 개수
 * @return array
 */
function newsGetMemberDayPosts($memberUid, $date, $page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    
    return getDbArray("
        SELECT d.*, bd.date, bd.bbs_id
        FROM bbs_day bd
        INNER JOIN bbs_data d ON bd.data_uid = d.uid
        WHERE bd.member_uid = ? AND bd.date = ?
        ORDER BY d.reg_date DESC
        LIMIT {$perPage} OFFSET {$offset}
    ", [$memberUid, $date]);
}

/**
 * 회원별 월별 게시물 목록 조회
 * 
 * @param int $memberUid 회원 UID
 * @param string $month 월 (Ym)
 * @param int $page 페이지 번호
 * @param int $perPage 페이지당 개수
 * @return array
 */
function newsGetMemberMonthPosts($memberUid, $month, $page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    
    return getDbArray("
        SELECT d.*, bm.date, bm.bbs_id
        FROM bbs_month bm
        INNER JOIN bbs_data d ON bm.data_uid = d.uid
        WHERE bm.member_uid = ? AND bm.date = ?
        ORDER BY d.reg_date DESC
        LIMIT {$perPage} OFFSET {$offset}
    ", [$memberUid, $month]);
}
