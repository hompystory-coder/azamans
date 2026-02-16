-- ========================================
-- 뉴스 성능 최적화 테이블 생성
-- BBS와 독립적으로 운영되는 News 전용 최적화 테이블
-- ========================================

-- 기존 테이블이 있다면 삭제 (주의: 데이터 손실 가능)
DROP TABLE IF EXISTS `news_day`;
DROP TABLE IF EXISTS `news_month`;
DROP TABLE IF EXISTS `news_index`;

-- 1. 일별 통계 테이블
CREATE TABLE `news_day` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `news_id` varchar(50) NOT NULL COMMENT '뉴스 게시판 ID',
    `data_uid` bigint(20) unsigned NOT NULL COMMENT '게시물 UID',
    `member_uid` bigint(20) unsigned DEFAULT NULL COMMENT '작성자 UID',
    `date` char(8) NOT NULL COMMENT '날짜 (Ymd)',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_news_data_date` (`news_id`, `data_uid`, `date`),
    KEY `idx_member_date` (`member_uid`, `date`),
    KEY `idx_news_date` (`news_id`, `date`),
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='뉴스 일별 통계';

-- 2. 월별 통계 테이블
CREATE TABLE `news_month` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `news_id` varchar(50) NOT NULL COMMENT '뉴스 게시판 ID',
    `data_uid` bigint(20) unsigned NOT NULL COMMENT '게시물 UID',
    `member_uid` bigint(20) unsigned DEFAULT NULL COMMENT '작성자 UID',
    `date` char(6) NOT NULL COMMENT '날짜 (Ym)',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_news_data_date` (`news_id`, `data_uid`, `date`),
    KEY `idx_member_date` (`member_uid`, `date`),
    KEY `idx_news_date` (`news_id`, `date`),
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='뉴스 월별 통계';

-- 3. 인덱싱 테이블 (빠른 조회용)
CREATE TABLE `news_index` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `news_id` varchar(50) NOT NULL COMMENT '뉴스 게시판 ID',
    `data_uid` bigint(20) unsigned NOT NULL COMMENT '게시물 UID',
    `member_uid` bigint(20) unsigned DEFAULT NULL COMMENT '작성자 UID',
    `category` varchar(100) DEFAULT NULL COMMENT '카테고리',
    `is_notice` char(1) DEFAULT 'N' COMMENT '공지사항 여부',
    `is_secret` char(1) DEFAULT 'N' COMMENT '비밀글 여부',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_news_data` (`news_id`, `data_uid`),
    KEY `idx_member` (`member_uid`),
    KEY `idx_category` (`news_id`, `category`),
    KEY `idx_notice` (`news_id`, `is_notice`, `created_at`),
    KEY `idx_created` (`news_id`, `created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='뉴스 인덱싱 테이블';

-- 완료 메시지
SELECT '✅ News 최적화 테이블 생성 완료!' AS message;
