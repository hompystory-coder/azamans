<?php
/**
 * Database Model Base Class
 * 데이터베이스 모델의 부모 클래스
 */

class DBModel {
    
    protected $pdo;
    protected $table;
    
    public function __construct() {
        $this->pdo = getDBConnection();
    }
    
    /**
     * 전체 레코드 조회
     * @param string $orderBy 정렬 (예: 'created_at DESC')
     * @param int $limit 제한
     * @return array
     */
    public function getAll($orderBy = null, $limit = null) {
        $sql = "SELECT * FROM `{$this->table}`";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return getDbArray($sql);
    }
    
    /**
     * ID로 레코드 조회
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        return getUidData("SELECT * FROM `{$this->table}` WHERE uid = ?", [$id]);
    }
    
    /**
     * 조건으로 레코드 조회 (단일)
     * @param array $where ['column' => 'value']
     * @return array|false
     */
    public function getByCondition($where) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $conditions[] = "`{$column}` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM `{$this->table}` WHERE " . implode(' AND ', $conditions) . " LIMIT 1";
        
        return getUidData($sql, $params);
    }
    
    /**
     * 조건으로 레코드 조회 (다중)
     * @param array $where ['column' => 'value']
     * @param string $orderBy
     * @param int $limit
     * @return array
     */
    public function getAllByCondition($where, $orderBy = null, $limit = null) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $conditions[] = "`{$column}` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM `{$this->table}` WHERE " . implode(' AND ', $conditions);
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return getDbArray($sql, $params);
    }
    
    /**
     * 레코드 삽입
     * @param array $data
     * @return int|false 마지막 삽입 ID
     */
    public function insert($data) {
        return getDbInsert($this->table, $data);
    }
    
    /**
     * 레코드 업데이트
     * @param int $id
     * @param array $data
     * @return int|false 영향받은 행 수
     */
    public function update($id, $data) {
        return getDbUpdate($this->table, $data, 'uid = ?', [$id]);
    }
    
    /**
     * 조건으로 레코드 업데이트
     * @param array $where
     * @param array $data
     * @return int|false
     */
    public function updateByCondition($where, $data) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $conditions[] = "`{$column}` = ?";
            $params[] = $value;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        return getDbUpdate($this->table, $data, $whereClause, $params);
    }
    
    /**
     * 레코드 삭제
     * @param int $id
     * @return int|false 삭제된 행 수
     */
    public function delete($id) {
        return getDbDelete($this->table, 'uid = ?', [$id]);
    }
    
    /**
     * 조건으로 레코드 삭제
     * @param array $where
     * @return int|false
     */
    public function deleteByCondition($where) {
        $conditions = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $conditions[] = "`{$column}` = ?";
            $params[] = $value;
        }
        
        $whereClause = implode(' AND ', $conditions);
        
        return getDbDelete($this->table, $whereClause, $params);
    }
    
    /**
     * 레코드 개수 조회
     * @param array $where (선택)
     * @return int
     */
    public function count($where = []) {
        if (empty($where)) {
            return getDbCnt("SELECT COUNT(*) FROM `{$this->table}`");
        }
        
        $conditions = [];
        $params = [];
        
        foreach ($where as $column => $value) {
            $conditions[] = "`{$column}` = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) FROM `{$this->table}` WHERE " . implode(' AND ', $conditions);
        
        return getDbCnt($sql, $params);
    }
    
    /**
     * 페이지네이션용 데이터 조회
     * @param int $page 현재 페이지
     * @param int $perPage 페이지당 항목 수
     * @param array $where 조건 (선택)
     * @param string $orderBy 정렬
     * @return array ['data' => array, 'total' => int, 'pages' => int]
     */
    public function paginate($page = 1, $perPage = 20, $where = [], $orderBy = null) {
        $offset = ($page - 1) * $perPage;
        
        // 전체 개수
        $total = $this->count($where);
        $totalPages = ceil($total / $perPage);
        
        // 데이터 조회
        if (empty($where)) {
            $sql = "SELECT * FROM `{$this->table}`";
            $params = [];
        } else {
            $conditions = [];
            $params = [];
            
            foreach ($where as $column => $value) {
                $conditions[] = "`{$column}` = ?";
                $params[] = $value;
            }
            
            $sql = "SELECT * FROM `{$this->table}` WHERE " . implode(' AND ', $conditions);
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";
        
        $data = getDbArray($sql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
}
