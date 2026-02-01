-- 워터마크 적용 대상 설정 추가
INSERT INTO site_config (config_key, config_value, config_group, config_description) VALUES
('watermark_target_board', 'Y', 'watermark', '게시판에 워터마크 적용 (Y/N)'),
('watermark_target_page', 'Y', 'watermark', '페이지에 워터마크 적용 (Y/N)'),
('thumbnail_delete_original', 'N', 'thumbnail', '썸네일 생성 후 원본 삭제 (Y/N)'),
('thumbnail_transparent_bg', 'white', 'thumbnail', '투명 배경 색상 (white/black)')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    config_description = VALUES(config_description);

-- 썸네일 기본값 업데이트
UPDATE site_config SET config_value = '900' WHERE config_key = 'thumb_big_width' AND (config_value = '' OR config_value IS NULL);
UPDATE site_config SET config_value = '640' WHERE config_key = 'thumb_middle_width' AND (config_value = '' OR config_value IS NULL);
UPDATE site_config SET config_value = '480' WHERE config_key = 'thumb_small_width' AND (config_value = '' OR config_value IS NULL);

SELECT '✅ 워터마크 및 썸네일 설정 업데이트 완료!' as message;
