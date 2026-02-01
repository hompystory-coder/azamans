<?php
/**
 * 게시판 최적화 헬퍼 함수
 * - bbs_day, bbs_month, bbs_index 테이블 관리
 */

/**
 * 게시물 등록 시 최적화 테이블에 데이터 추가
 */
function bbsInsertOptimizationData($bbsId, $dataUid, $memberUid = null, $category = null, $isNotice = 'N', $isSecret = 'N') {
    try {
        $date = date('Ymd');
        $month = date('Ym');
        
        // 1. bbs_day 추가
        $dayData = [
            'bbs_id' => $bbsId,
            'data_uid' => $dataUid,
            'member_uid' => $memberUid,
            'date' => $date
        ];
        getDbInsert('bbs_day', $dayData);
        
        // 2. bbs_month 추가
        $monthData = [
            'bbs_id' => $bbsId,
            'data_uid' => $dataUid,
            'member_uid' => $memberUid,
            'date' => $month
        ];
        getDbInsert('bbs_month', $monthData);
        
        // 3. bbs_index 추가
        $indexData = [
            'bbs_id' => $bbsId,
            'data_uid' => $dataUid,
            'member_uid' => $memberUid,
            'category' => $category,
            'is_notice' => $isNotice,
            'is_secret' => $isSecret
        ];
        getDbInsert('bbs_index', $indexData);
        
        return true;
    } catch (Exception $e) {
        error_log('bbsInsertOptimizationData Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * 게시물 수정 시 최적화 테이블 업데이트
 */
function bbsUpdateOptimizationData($bbsId, $dataUid, $category = null, $isNotice = 'N', $isSecret = 'N') {
    try {
        // bbs_index만 업데이트 (날짜 정보는 변경 안 함)
        $updateData = [
            'category' => $category,
            'is_notice' => $isNotice,
            'is_secret' => $isSecret
        ];
        
        getDbUpdate('bbs_index', $updateData, 'bbs_id = ? AND data_uid = ?', [$bbsId, $dataUid]);
        
        return true;
    } catch (Exception $e) {
        error_log('bbsUpdateOptimizationData Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * 게시물 삭제 시 최적화 테이블에서 제거
 */
function bbsDeleteOptimizationData($bbsId, $dataUid) {
    try {
        // 1. bbs_day 삭제
        getDbDelete('bbs_day', 'bbs_id = ? AND data_uid = ?', [$bbsId, $dataUid]);
        
        // 2. bbs_month 삭제
        getDbDelete('bbs_month', 'bbs_id = ? AND data_uid = ?', [$bbsId, $dataUid]);
        
        // 3. bbs_index 삭제
        getDbDelete('bbs_index', 'bbs_id = ? AND data_uid = ?', [$bbsId, $dataUid]);
        
        return true;
    } catch (Exception $e) {
        error_log('bbsDeleteOptimizationData Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * bbs_index 기반 리스트 조회 (일반 리스트)
 */
function bbsGetListFromIndex($bbsId, $page = 1, $perPage = 20, $category = null, $isNoticeOnly = false) {
    $offset = ($page - 1) * $perPage;
    
    // WHERE 조건 생성
    $where = ['i.bbs_id = ?'];
    $params = [$bbsId];
    
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
function bbsGetCountFromIndex($bbsId, $category = null, $isNoticeOnly = false) {
    $where = ['bbs_id = ?'];
    $params = [$bbsId];
    
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
function bbsSearchFromData($bbsId, $searchField, $searchKeyword, $page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    
    $where = ['bbs_id = ?'];
    $params = [$bbsId];
    
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
function bbsGetMemberMonthlyCount($memberUid, $bbsId = null, $yearMonth = null) {
    $where = ['member_uid = ?'];
    $params = [$memberUid];
    
    if ($bbsId) {
        $where[] = 'bbs_id = ?';
        $params[] = $bbsId;
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
function bbsGetMemberDailyCount($memberUid, $bbsId = null, $date = null) {
    $where = ['member_uid = ?'];
    $params = [$memberUid];
    
    if ($bbsId) {
        $where[] = 'bbs_id = ?';
        $params[] = $bbsId;
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
