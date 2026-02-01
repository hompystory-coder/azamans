-- ========================================
-- 게시판 데이터 초기화 (테스트용)
-- ⚠️ 주의: 모든 게시판 데이터가 삭제됩니다!
-- ========================================

-- 외래 키 체크 비활성화
SET FOREIGN_KEY_CHECKS = 0;

-- 게시판 관련 모든 테이블 초기화
TRUNCATE TABLE bbs_data;
TRUNCATE TABLE bbs_upload;
TRUNCATE TABLE bbs_comment;
TRUNCATE TABLE bbs_like;

-- 최적화 테이블 초기화
TRUNCATE TABLE bbs_day;
TRUNCATE TABLE bbs_month;
TRUNCATE TABLE bbs_index;

-- 외래 키 체크 활성화
SET FOREIGN_KEY_CHECKS = 1;

-- 완료 메시지
SELECT '✅ 모든 게시판 데이터가 초기화되었습니다!' AS message;
SELECT '이제 게시물을 등록하여 테스트하세요.' AS next_step;
