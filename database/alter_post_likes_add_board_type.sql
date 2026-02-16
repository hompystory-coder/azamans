-- ================================================================================
-- post_likes 테이블에 board_type 컬럼 추가
-- BBS와 News를 구분하기 위한 필드
-- ================================================================================

-- board_type 컬럼 추가 (없을 경우에만)
ALTER TABLE `post_likes` 
ADD COLUMN IF NOT EXISTS `board_type` VARCHAR(10) NOT NULL DEFAULT 'bbs' COMMENT '게시판 타입 (bbs, news)' AFTER `post_uid`;

-- 기존 UNIQUE KEY 삭제 (post_uid, member_uid만으로는 구분 불가)
-- ALTER TABLE `post_likes` DROP INDEX IF EXISTS `unique_post_member`;

-- 새로운 UNIQUE KEY 추가 (board_type, post_uid, member_uid)
ALTER TABLE `post_likes` 
ADD UNIQUE KEY IF NOT EXISTS `unique_board_post_member` (`board_type`, `post_uid`, `member_uid`);

-- 인덱스 추가 (조회 성능 향상)
ALTER TABLE `post_likes` 
ADD INDEX IF NOT EXISTS `idx_board_post` (`board_type`, `post_uid`);

-- 완료 메시지
SELECT '✅ post_likes 테이블에 board_type 컬럼 추가 완료!' AS message;
