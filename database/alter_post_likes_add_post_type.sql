-- ================================================================================
-- post_likes 테이블에 post_type 컬럼 추가
-- BBS와 News를 구분하기 위한 필드
-- ================================================================================

-- 1. post_type 컬럼 추가
ALTER TABLE `post_likes` 
ADD COLUMN `post_type` VARCHAR(10) NOT NULL DEFAULT 'bbs' COMMENT '게시물 타입 (bbs, news)' AFTER `post_uid`;

-- 2. 기존 UNIQUE KEY 삭제
ALTER TABLE `post_likes` 
DROP INDEX `unique_like`;

-- 3. 새로운 UNIQUE KEY 추가 (post_type 포함)
ALTER TABLE `post_likes` 
ADD UNIQUE KEY `unique_type_post_member` (`post_type`, `post_uid`, `member_uid`);

-- 4. 인덱스 추가 (조회 성능 향상)
ALTER TABLE `post_likes` 
ADD INDEX `idx_type_post` (`post_type`, `post_uid`);

-- 완료 메시지
SELECT '✅ post_likes 테이블에 post_type 컬럼 추가 완료!' AS message;
