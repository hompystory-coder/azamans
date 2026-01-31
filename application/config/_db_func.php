<?php
/**
 * Database Helper Functions
 * DB 관련 도우미 함수 모음 (PDO 기반)
 */

require_once __DIR__ . '/_db_info.php';

/**
 * SELECT 쿼리 실행 (단일 행)
 * @param string $sql
 * @param array $params
 * @return array|false
 */
function getUidData($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getUidData Error: " . $e->getMessage());
        return false;
    }
}

/**
 * SELECT 쿼리 실행 (단일 행) - getUidData의 별칭
 */
function getDbData($sql, $params = []) {
    return getUidData($sql, $params);
}

/**
 * SELECT 쿼리 실행 (다중 행)
 * @param string $sql
 * @param array $params
 * @return array
 */
function getDbArray($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("getDbArray Error: " . $e->getMessage());
        return [];
    }
}

/**
 * 행 개수 반환
 * @param string $sql
 * @param array $params
 * @return int
 */
function getDbRows($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("getDbRows Error: " . $e->getMessage());
        return 0;
    }
}

/**
 * COUNT 쿼리 결과 반환
 * @param string $sql
 * @param array $params
 * @return int
 */
function getDbCnt($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_NUM);
        return (int)($result[0] ?? 0);
    } catch (PDOException $e) {
        error_log("getDbCnt Error: " . $e->getMessage());
        return 0;
    }
}

/**
 * SELECT 쿼리 실행 (PDOStatement 반환)
 * @param string $sql
 * @param array $params
 * @return PDOStatement|false
 */
function getDbSelect($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("getDbSelect Error: " . $e->getMessage());
        return false;
    }
}

/**
 * INSERT 쿼리 실행
 * @param string $table 테이블명
 * @param array $data 삽입할 데이터 (key => value)
 * @return int|false 마지막 삽입 ID 또는 false
 */
function getDbInsert($table, $data) {
    try {
        $pdo = getDBConnection();
        
        $columns = array_keys($data);
        $values = array_values($data);
        
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) 
                VALUES (" . implode(', ', array_fill(0, count($values), '?')) . ")";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("getDbInsert Error: " . $e->getMessage());
        return false;
    }
}

/**
 * UPDATE 쿼리 실행
 * @param string $table 테이블명
 * @param array $data 업데이트할 데이터 (key => value)
 * @param string $where WHERE 조건 (예: "id = ?")
 * @param array $whereParams WHERE 조건의 파라미터
 * @return int|false 영향받은 행 수 또는 false
 */
function getDbUpdate($table, $data, $where, $whereParams = []) {
    try {
        $pdo = getDBConnection();
        
        $columns = array_keys($data);
        $values = array_values($data);
        
        $setClause = implode(', ', array_map(function($col) {
            return "`{$col}` = ?";
        }, $columns));
        
        $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$where}";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($values, $whereParams));
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("getDbUpdate Error: " . $e->getMessage());
        return false;
    }
}

/**
 * DELETE 쿼리 실행
 * @param string $table 테이블명
 * @param string $where WHERE 조건 (예: "id = ?")
 * @param array $params WHERE 조건의 파라미터
 * @return int|false 삭제된 행 수 또는 false
 */
function getDbDelete($table, $where, $params = []) {
    try {
        $pdo = getDBConnection();
        
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("getDbDelete Error: " . $e->getMessage());
        return false;
    }
}

/**
 * SQL Injection 필터링 (추가 보안 레이어)
 * @param string $str
 * @return string
 */
function getSqlFilter($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

/**
 * 트랜잭션 시작
 */
function dbBeginTransaction() {
    try {
        $pdo = getDBConnection();
        return $pdo->beginTransaction();
    } catch (PDOException $e) {
        error_log("dbBeginTransaction Error: " . $e->getMessage());
        return false;
    }
}

/**
 * 트랜잭션 커밋
 */
function dbCommit() {
    try {
        $pdo = getDBConnection();
        return $pdo->commit();
    } catch (PDOException $e) {
        error_log("dbCommit Error: " . $e->getMessage());
        return false;
    }
}

/**
 * 트랜잭션 롤백
 */
function dbRollback() {
    try {
        $pdo = getDBConnection();
        return $pdo->rollBack();
    } catch (PDOException $e) {
        error_log("dbRollback Error: " . $e->getMessage());
        return false;
    }
}
