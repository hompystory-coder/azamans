-- 사이트 설정 테이블 생성
CREATE TABLE IF NOT EXISTS site_config (
    config_key VARCHAR(100) PRIMARY KEY COMMENT '설정 키',
    config_value TEXT COMMENT '설정 값',
    config_group VARCHAR(50) DEFAULT 'general' COMMENT '설정 그룹',
    config_description VARCHAR(255) COMMENT '설정 설명',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='사이트 설정';

-- 기본 설정 데이터 삽입
INSERT INTO site_config (config_key, config_value, config_group, config_description) VALUES
-- 이미지 업로드 설정
('image_max_width', '900', 'image', '이미지 최대 가로폭 (px)'),
('image_quality', '100', 'image', '이미지 품질 (1-100%)'),

-- 썸네일 크기 설정
('thumb_big_width', '800', 'thumbnail', '큰 썸네일 가로 크기 (px)'),
('thumb_big_height', '600', 'thumbnail', '큰 썸네일 세로 크기 (px)'),
('thumb_middle_width', '400', 'thumbnail', '중간 썸네일 가로 크기 (px)'),
('thumb_middle_height', '300', 'thumbnail', '중간 썸네일 세로 크기 (px)'),
('thumb_small_width', '200', 'thumbnail', '작은 썸네일 가로 크기 (px)'),
('thumb_small_height', '150', 'thumbnail', '작은 썸네일 세로 크기 (px)'),
('thumb_quality', '100', 'thumbnail', '썸네일 해상도 (1-100%)'),

-- 워터마크 설정
('watermark_enabled', 'N', 'watermark', '워터마크 사용 여부 (Y/N)'),
('watermark_position', '5', 'watermark', '워터마크 위치 (1:좌상단, 2:우상단, 3:중앙, 4:좌하단, 5:우하단)'),
('watermark_image', '', 'watermark', '워터마크 이미지 파일명'),
('watermark_opacity', '80', 'watermark', '워터마크 투명도 (0-100%)'),

-- 이미지 처리 옵션
('image_sharpen', 'N', 'image', '이미지 샤픈 적용 여부 (Y/N)'),
('image_sharpen_value', '80/0.5/3', 'image', '이미지 샤픈 값 (amount/radius/threshold)')
ON DUPLICATE KEY UPDATE config_value = VALUES(config_value);

SELECT '✅ site_config 테이블 및 기본 데이터 생성 완료!' as message;
