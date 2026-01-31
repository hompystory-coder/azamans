<?php
/**
 * Point Model
 * 포인트 시스템 모델
 */

require_once __DIR__ . '/DBModel.php';

class PointModel extends DBModel {
    
    protected $table = 'point_history';
    
    /**
     * 포인트 적립
     */
    public function addPoint($memberUid, $point, $reason) {
        if ($point <= 0) {
            return false;
        }
        
        // 현재 포인트 조회
        $currentPoint = getDbCnt("SELECT point FROM member WHERE uid = ?", [$memberUid]) ?: 0;
        $newBalance = $currentPoint + $point;
        
        // point_history 테이블에 기록
        $historyData = [
            'user_uid' => $memberUid,
            'amount' => $point,
            'balance' => $newBalance,
            'type' => 'earn',
            'description' => $reason
        ];
        
        $historyId = getDbInsert('point_history', $historyData);
        
        if ($historyId) {
            // member 테이블의 포인트 업데이트
            getDbUpdate('member', 
                ['point' => $newBalance], 
                'uid = ?', 
                [$memberUid]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 포인트 차감
     */
    public function deductPoint($memberUid, $point, $reason) {
        if ($point <= 0) {
            return false;
        }
        
        // 현재 포인트 확인
        $currentPoint = getDbCnt("SELECT point FROM member WHERE uid = ?", [$memberUid]) ?: 0;
        
        if ($currentPoint < $point) {
            return false; // 포인트 부족
        }
        
        $newBalance = $currentPoint - $point;
        
        // point_history 테이블에 기록
        $historyData = [
            'user_uid' => $memberUid,
            'amount' => -$point,
            'balance' => $newBalance,
            'type' => 'use',
            'description' => $reason
        ];
        
        $historyId = getDbInsert('point_history', $historyData);
        
        if ($historyId) {
            // member 테이블의 포인트 업데이트
            getDbUpdate('member', 
                ['point' => $newBalance], 
                'uid = ?', 
                [$memberUid]
            );
            
            return true;
        }
        
        return false;
    }
    
    /**
     * 포인트 내역 조회
     */
    public function getPointHistory($memberUid, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $total = getDbCnt("SELECT COUNT(*) FROM point_history WHERE user_uid = ?", [$memberUid]);
        
        $history = getDbArray("
            SELECT * FROM point_history 
            WHERE user_uid = ?
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ", [$memberUid]);
        
        return [
            'history' => $history,
            'total' => $total,
            'pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * 회원 포인트 조회
     */
    public function getMemberPoint($memberUid) {
        return getDbCnt("SELECT point FROM member WHERE uid = ?", [$memberUid]) ?: 0;
    }
    
    /**
     * 포인트 정책 - 게시글 작성
     */
    public function rewardPost($memberUid, $postUid) {
        return $this->addPoint($memberUid, 10, '게시글 작성');
    }
    
    /**
     * 포인트 정책 - 댓글 작성
     */
    public function rewardComment($memberUid, $commentUid) {
        return $this->addPoint($memberUid, 5, '댓글 작성');
    }
    
    /**
     * 포인트 정책 - 로그인
     */
    public function rewardLogin($memberUid) {
        // 오늘 이미 로그인 포인트를 받았는지 확인
        $today = date('Y-m-d');
        $exists = getUidData("
            SELECT uid FROM point_history 
            WHERE user_uid = ? 
            AND description = '일일 로그인' 
            AND DATE(created_at) = ?
            LIMIT 1
        ", [$memberUid, $today]);
        
        if ($exists) {
            return false; // 이미 받음
        }
        
        return $this->addPoint($memberUid, 2, '일일 로그인');
    }
}
