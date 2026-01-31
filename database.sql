-- MVC Framework Database Schema

-- 게시판 목록 테이블
CREATE TABLE IF NOT EXISTS `bbs_list` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` varchar(50) NOT NULL COMMENT '게시판 ID',
  `board_name` varchar(100) NOT NULL COMMENT '게시판 이름',
  `board_skin` varchar(50) DEFAULT 'default' COMMENT '게시판 스킨',
  `posts_per_page` int(11) DEFAULT 20 COMMENT '페이지당 게시물 수',
  `read_level` int(11) DEFAULT 1 COMMENT '읽기 권한 레벨',
  `write_level` int(11) DEFAULT 1 COMMENT '쓰기 권한 레벨',
  `comment_level` int(11) DEFAULT 1 COMMENT '댓글 권한 레벨',
  `use_comment` enum('Y','N') DEFAULT 'Y' COMMENT '댓글 사용',
  `use_category` enum('Y','N') DEFAULT 'N' COMMENT '카테고리 사용',
  `categories` text COMMENT '카테고리 목록 (JSON)',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `board_id` (`board_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 게시판 인덱스 테이블
CREATE TABLE IF NOT EXISTS `bbs_index` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `writer` varchar(50) NOT NULL,
  `writer_uid` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT '비회원 비밀번호',
  `views` int(11) DEFAULT 0,
  `likes` int(11) DEFAULT 0,
  `comments` int(11) DEFAULT 0,
  `is_notice` enum('Y','N') DEFAULT 'N',
  `is_secret` enum('Y','N') DEFAULT 'N',
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('active','deleted') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `board_id` (`board_id`),
  KEY `writer_uid` (`writer_uid`),
  KEY `created_at` (`created_at`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 게시물 데이터 테이블 (추가 정보)
CREATE TABLE IF NOT EXISTS `bbs_data` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `post_uid` int(11) NOT NULL,
  `attachments` text COMMENT '첨부파일 정보 (JSON)',
  `tags` varchar(500) DEFAULT NULL,
  `meta_data` text COMMENT '메타 데이터 (JSON)',
  PRIMARY KEY (`uid`),
  KEY `post_uid` (`post_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 월별 게시물 통계
CREATE TABLE IF NOT EXISTS `bbs_month` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` varchar(50) NOT NULL,
  `year_month` varchar(7) NOT NULL COMMENT 'YYYY-MM',
  `post_count` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `board_month` (`board_id`,`year_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 일별 게시물 통계
CREATE TABLE IF NOT EXISTS `bbs_day` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `post_count` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `board_date` (`board_id`,`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 댓글 테이블
CREATE TABLE IF NOT EXISTS `bbs_comment` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `post_uid` int(11) NOT NULL,
  `parent_uid` int(11) DEFAULT NULL COMMENT '대댓글인 경우 부모 댓글 UID',
  `content` text NOT NULL,
  `writer` varchar(50) NOT NULL,
  `writer_uid` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT '비회원 비밀번호',
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('active','deleted') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `post_uid` (`post_uid`),
  KEY `parent_uid` (`parent_uid`),
  KEY `writer_uid` (`writer_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 회원 레벨 테이블
CREATE TABLE IF NOT EXISTS `member_level` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL,
  `level_description` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 회원 테이블
CREATE TABLE IF NOT EXISTS `member` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `profile_image` varchar(255) DEFAULT NULL,
  `intro` text COMMENT '자기소개',
  `points` int(11) DEFAULT 0,
  `post_count` int(11) DEFAULT 0,
  `comment_count` int(11) DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `login_count` int(11) DEFAULT 0,
  `status` enum('active','inactive','suspended','deleted') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `level` (`level`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 관리자 테이블
CREATE TABLE IF NOT EXISTS `admin` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `member_uid` int(11) NOT NULL,
  `role` varchar(50) DEFAULT 'admin' COMMENT '관리자 역할',
  `permissions` text COMMENT '권한 목록 (JSON)',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `member_uid` (`member_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 관리자 설정 테이블
CREATE TABLE IF NOT EXISTS `admin_config` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) NOT NULL,
  `config_value` text,
  `config_group` varchar(50) DEFAULT 'general',
  `config_type` varchar(20) DEFAULT 'text' COMMENT 'text, number, boolean, json',
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `config_key` (`config_key`),
  KEY `config_group` (`config_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 게시판 관리자 설정
CREATE TABLE IF NOT EXISTS `admin_bbs` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `board_id` varchar(50) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  UNIQUE KEY `board_config` (`board_id`,`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 레이아웃 관리 테이블
CREATE TABLE IF NOT EXISTS `admin_layout` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `layout_name` varchar(50) NOT NULL,
  `layout_type` varchar(20) NOT NULL COMMENT 'header, footer, sidebar',
  `content` longtext,
  `is_active` enum('Y','N') DEFAULT 'Y',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `layout_type` (`layout_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- IP 차단 테이블
CREATE TABLE IF NOT EXISTS `ip_blocks` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`),
  KEY `ip_address` (`ip_address`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 기본 데이터 삽입

-- 회원 레벨 기본 데이터
INSERT INTO `member_level` (`level`, `level_name`, `level_description`) VALUES
(1, '일반회원', '일반 회원 등급'),
(5, '정회원', '정회원 등급'),
(9, '관리자', '관리자 등급'),
(10, '최고관리자', '최고관리자 등급')
ON DUPLICATE KEY UPDATE level=level;

-- 사이트 기본 설정
INSERT INTO `admin_config` (`config_key`, `config_value`, `config_group`, `config_type`, `description`) VALUES
('site_name', 'MVC Framework', 'general', 'text', '사이트 이름'),
('site_url', 'https://mvc.neuralgrid.kr', 'general', 'text', '사이트 URL'),
('site_email', 'admin@example.com', 'general', 'text', '사이트 이메일'),
('site_description', 'PHP MVC Framework', 'general', 'text', '사이트 설명'),
('timezone', 'Asia/Seoul', 'general', 'text', '타임존'),
('posts_per_page', '20', 'board', 'number', '페이지당 게시물 수'),
('use_captcha', 'N', 'security', 'boolean', '캡챠 사용 여부')
ON DUPLICATE KEY UPDATE config_key=config_key;

-- 기본 관리자 계정 (username: admin, password: admin1234)
INSERT INTO `member` (`username`, `password`, `email`, `name`, `level`, `status`) VALUES
('admin', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5NzX0rqDdGF2i', 'admin@example.com', '관리자', 10, 'active')
ON DUPLICATE KEY UPDATE username=username;
