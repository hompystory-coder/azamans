<?php
/**
 * Notification Helper
 * 알림 시스템
 */

class Notification {
    
    /**
     * 알림 생성
     */
    public static function create($userUid, $type, $title, $message = '', $link = '') {
        return getDbInsert('notifications', [
            'user_uid' => $userUid,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 'N'
        ]);
    }
    
    /**
     * 사용자의 안 읽은 알림 개수
     */
    public static function getUnreadCount($userUid) {
        return getDbCnt("SELECT COUNT(*) FROM notifications WHERE user_uid = ? AND is_read = 'N'", [$userUid]);
    }
    
    /**
     * 사용자의 알림 목록
     */
    public static function getList($userUid, $limit = 20) {
        return getDbArray("
            SELECT * FROM notifications 
            WHERE user_uid = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$userUid, $limit]);
    }
    
    /**
     * 알림 읽음 처리
     */
    public static function markAsRead($uid) {
        return getDbUpdate('notifications', ['is_read' => 'Y'], 'uid = ?', [$uid]);
    }
    
    /**
     * 모든 알림 읽음 처리
     */
    public static function markAllAsRead($userUid) {
        return getDbUpdate('notifications', ['is_read' => 'Y'], 'user_uid = ?', [$userUid]);
    }
    
    /**
     * 댓글 알림
     */
    public static function notifyComment($postWriterUid, $commenterName, $postTitle, $postLink) {
        if (!$postWriterUid) return false;
        
        return self::create(
            $postWriterUid,
            'comment',
            '새 댓글이 달렸습니다',
            "{$commenterName}님이 '{$postTitle}'에 댓글을 작성했습니다.",
            $postLink
        );
    }
    
    /**
     * 좋아요 알림
     */
    public static function notifyLike($postWriterUid, $likerName, $postTitle, $postLink) {
        if (!$postWriterUid) return false;
        
        return self::create(
            $postWriterUid,
            'like',
            '좋아요를 받았습니다',
            "{$likerName}님이 '{$postTitle}'을 좋아합니다.",
            $postLink
        );
    }
    
    /**
     * 멘션 알림
     */
    public static function notifyMention($mentionedUid, $mentionerName, $content, $link) {
        return self::create(
            $mentionedUid,
            'mention',
            '회원님이 언급되었습니다',
            "{$mentionerName}님이 회원님을 언급했습니다.",
            $link
        );
    }
    
    /**
     * 시스템 알림
     */
    public static function notifySystem($userUid, $title, $message, $link = '') {
        return self::create($userUid, 'system', $title, $message, $link);
    }
}

/**
 * Point Helper
 * 포인트 시스템
 */

class Point {
    
    // 포인트 정책
    const POINTS = [
        'login' => 10,          // 로그인
        'post' => 50,           // 게시물 작성
        'comment' => 10,        // 댓글 작성
        'like_receive' => 5,    // 좋아요 받음
        'post_delete' => -50,   // 게시물 삭제
        'comment_delete' => -10 // 댓글 삭제
    ];
    
    /**
     * 포인트 적립/차감
     */
    public static function add($userUid, $amount, $type, $description = '') {
        // 현재 포인트 조회
        $currentPoint = getUidData("SELECT point FROM member WHERE uid = ?", [$userUid]);
        $balance = ($currentPoint['point'] ?? 0) + $amount;
        
        // 포인트 내역 저장
        getDbInsert('point_history', [
            'user_uid' => $userUid,
            'amount' => $amount,
            'balance' => $balance,
            'type' => $type,
            'description' => $description
        ]);
        
        // 회원 포인트 업데이트
        return getDbUpdate('member', ['point' => $balance], 'uid = ?', [$userUid]);
    }
    
    /**
     * 포인트 히스토리
     */
    public static function getHistory($userUid, $limit = 50) {
        return getDbArray("
            SELECT * FROM point_history 
            WHERE user_uid = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$userUid, $limit]);
    }
    
    /**
     * 사용자 포인트 조회
     */
    public static function getBalance($userUid) {
        $result = getUidData("SELECT point FROM member WHERE uid = ?", [$userUid]);
        return $result['point'] ?? 0;
    }
    
    /**
     * 게시물 작성 포인트
     */
    public static function earnPost($userUid) {
        return self::add($userUid, self::POINTS['post'], 'post', '게시물 작성');
    }
    
    /**
     * 댓글 작성 포인트
     */
    public static function earnComment($userUid) {
        return self::add($userUid, self::POINTS['comment'], 'comment', '댓글 작성');
    }
    
    /**
     * 로그인 포인트
     */
    public static function earnLogin($userUid) {
        // 오늘 이미 로그인 포인트를 받았는지 확인
        $today = date('Y-m-d');
        $alreadyEarned = getUidData("
            SELECT uid FROM point_history 
            WHERE user_uid = ? AND type = 'login' AND DATE(created_at) = ?
        ", [$userUid, $today]);
        
        if (!$alreadyEarned) {
            return self::add($userUid, self::POINTS['login'], 'login', '일일 로그인');
        }
        
        return false;
    }
    
    /**
     * 좋아요 받기 포인트
     */
    public static function earnLike($userUid) {
        return self::add($userUid, self::POINTS['like_receive'], 'like', '좋아요 받음');
    }
    
    /**
     * 게시물 삭제 포인트 차감
     */
    public static function deductPost($userUid) {
        return self::add($userUid, self::POINTS['post_delete'], 'post_delete', '게시물 삭제');
    }
    
    /**
     * 댓글 삭제 포인트 차감
     */
    public static function deductComment($userUid) {
        return self::add($userUid, self::POINTS['comment_delete'], 'comment_delete', '댓글 삭제');
    }
}
