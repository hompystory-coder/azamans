-- 기존 테이블 삭제
DROP TABLE IF EXISTS `bbs_day`;
DROP TABLE IF EXISTS `bbs_month`;
DROP TABLE IF EXISTS `bbs_index`;

-- ========================================
-- 게시판 성능 최적화 테이블 재생성
-- ========================================

-- 1. 일별 통계 테이블
CREATE TABLE `bbs_day` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `bbs_id` varchar(50) NOT NULL COMMENT '게시판 ID',
    `data_uid` bigint(20) unsigned NOT NULL COMMENT '게시물 UID',
    `member_uid` bigint(20) unsigned DEFAULT NULL COMMENT '작성자 UID',
    `date` char(8) NOT NULL COMMENT '날짜 (Ymd)',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_bbs_data_date` (`bbs_id`, `data_uid`, `date`),
    KEY `idx_member_date` (`member_uid`, `date`),
    KEY `idx_bbs_date` (`bbs_id`, `date`),
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 일별 통계';

-- 2. 월별 통계 테이블
CREATE TABLE `bbs_month` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `bbs_id` varchar(50) NOT NULL COMMENT '게시판 ID',
    `data_uid` bigint(20) unsigned NOT NULL COMMENT '게시물 UID',
    `member_uid` bigint(20) unsigned DEFAULT NULL COMMENT '작성자 UID',
    `date` char(6) NOT NULL COMMENT '날짜 (Ym)',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_bbs_data_date` (`bbs_id`, `data_uid`, `date`),
    KEY `idx_member_date` (`member_uid`, `date`),
    KEY `idx_bbs_date` (`bbs_id`, `date`),
    KEY `idx_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 월별 통계';

-- 3. 인덱싱 테이블 (빠른 조회용)
CREATE TABLE `bbs_index` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `bbs_id` varchar(50) NOT NULL COMMENT '게시판 ID',
    `data_uid` bigint(20) unsigned NOT NULL COMMENT '게시물 UID',
    `member_uid` bigint(20) unsigned DEFAULT NULL COMMENT '작성자 UID',
    `category` varchar(100) DEFAULT NULL COMMENT '카테고리',
    `is_notice` char(1) DEFAULT 'N' COMMENT '공지사항 여부',
    `is_secret` char(1) DEFAULT 'N' COMMENT '비밀글 여부',
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_bbs_data` (`bbs_id`, `data_uid`),
    KEY `idx_member` (`member_uid`),
    KEY `idx_category` (`bbs_id`, `category`),
    KEY `idx_notice` (`bbs_id`, `is_notice`, `created_at`),
    KEY `idx_created` (`bbs_id`, `created_at` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판 인덱싱 테이블';

-- 완료 메시지
SELECT '✅ 최적화 테이블 재생성 완료!' AS message;
