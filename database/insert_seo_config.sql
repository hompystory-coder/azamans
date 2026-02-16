-- SEO 메타 태그 설정 추가
-- 작성일: 2026-02-16

-- 1. SEO 기본 설정 추가
INSERT INTO site_config (config_key, config_value, config_group, config_description) VALUES
('seo_title', 'MVC Framework', 'seo', '기본 사이트 제목'),
('seo_description', 'PHP MVC Framework로 구축된 웹사이트', 'seo', '기본 사이트 설명'),
('seo_keywords', 'PHP, MVC, Framework, 웹개발', 'seo', '기본 키워드'),
('seo_author', 'Admin', 'seo', '저자명'),
('seo_image', '', 'seo', '기본 OG 이미지 URL'),
('seo_twitter_handle', '@YourTwitter', 'seo', 'Twitter 핸들'),
('favicon_ico', '/favicon.ico', 'seo', 'Favicon ICO 경로'),
('favicon_apple', '/apple-touch-icon.png', 'seo', 'Apple Touch Icon 경로')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    config_description = VALUES(config_description);

-- 확인
SELECT '✅ SEO 설정 추가 완료!' as result;
SELECT config_key, config_value FROM site_config WHERE config_group = 'seo';
