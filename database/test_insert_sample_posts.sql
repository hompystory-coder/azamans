-- 테스트용 샘플 게시물 삽입
-- 자유게시판(free)에 10개의 샘플 게시물 추가

-- 1번 게시물
INSERT INTO bbs_data (bbs_id, title, content, name, member_uid, category, is_notice, is_secret, view_count, reg_date, ip_address)
VALUES ('free', '첫 번째 테스트 게시물', '이것은 테스트 내용입니다.', '관리자', 1, '일반', 'N', 'N', 0, NOW(), '127.0.0.1');

SET @last_id = LAST_INSERT_ID();
SET @today = DATE_FORMAT(NOW(), '%Y%m%d');
SET @month = DATE_FORMAT(NOW(), '%Y%m');

-- bbs_day 삽입
INSERT INTO bbs_day (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @today);

-- bbs_month 삽입
INSERT INTO bbs_month (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @month);

-- bbs_index 삽입
INSERT INTO bbs_index (bbs_id, data_uid, member_uid, category, is_notice, is_secret)
VALUES ('free', @last_id, 1, '일반', 'N', 'N');

-- 2번 게시물
INSERT INTO bbs_data (bbs_id, title, content, name, member_uid, category, is_notice, is_secret, view_count, reg_date, ip_address)
VALUES ('free', '두 번째 테스트 게시물', '두 번째 테스트 내용입니다.', '관리자', 1, '질문', 'N', 'N', 0, NOW(), '127.0.0.1');

SET @last_id = LAST_INSERT_ID();

INSERT INTO bbs_day (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @today);

INSERT INTO bbs_month (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @month);

INSERT INTO bbs_index (bbs_id, data_uid, member_uid, category, is_notice, is_secret)
VALUES ('free', @last_id, 1, '질문', 'N', 'N');

-- 3번 게시물
INSERT INTO bbs_data (bbs_id, title, content, name, member_uid, category, is_notice, is_secret, view_count, reg_date, ip_address)
VALUES ('free', '세 번째 테스트 게시물', '세 번째 테스트 내용입니다.', '관리자', 1, '정보', 'N', 'N', 0, NOW(), '127.0.0.1');

SET @last_id = LAST_INSERT_ID();

INSERT INTO bbs_day (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @today);

INSERT INTO bbs_month (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @month);

INSERT INTO bbs_index (bbs_id, data_uid, member_uid, category, is_notice, is_secret)
VALUES ('free', @last_id, 1, '정보', 'N', 'N');

-- 4번 게시물 (공지사항)
INSERT INTO bbs_data (bbs_id, title, content, name, member_uid, category, is_notice, is_secret, view_count, reg_date, ip_address)
VALUES ('free', '[공지] 테스트 공지사항', '공지사항 내용입니다.', '관리자', 1, '공지', 'Y', 'N', 0, NOW(), '127.0.0.1');

SET @last_id = LAST_INSERT_ID();

INSERT INTO bbs_day (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @today);

INSERT INTO bbs_month (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @month);

INSERT INTO bbs_index (bbs_id, data_uid, member_uid, category, is_notice, is_secret)
VALUES ('free', @last_id, 1, '공지', 'Y', 'N');

-- 5번 게시물 (비밀글)
INSERT INTO bbs_data (bbs_id, title, content, name, member_uid, category, is_notice, is_secret, view_count, reg_date, ip_address)
VALUES ('free', '비밀 테스트 게시물', '비밀 내용입니다.', '관리자', 1, '일반', 'N', 'Y', 0, NOW(), '127.0.0.1');

SET @last_id = LAST_INSERT_ID();

INSERT INTO bbs_day (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @today);

INSERT INTO bbs_month (bbs_id, data_uid, member_uid, date)
VALUES ('free', @last_id, 1, @month);

INSERT INTO bbs_index (bbs_id, data_uid, member_uid, category, is_notice, is_secret)
VALUES ('free', @last_id, 1, '일반', 'N', 'Y');

SELECT '✅ 샘플 게시물 5개 삽입 완료!' as message;
