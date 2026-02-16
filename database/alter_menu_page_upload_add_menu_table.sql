-- menu_page_upload 테이블에 menu_table 컬럼 추가
-- 작성일: 2026-02-16

-- 1. menu_table 컬럼 추가 (기본값 'header')
ALTER TABLE menu_page_upload 
ADD COLUMN menu_table enum('header','footer') NOT NULL DEFAULT 'header' 
COMMENT '메뉴 타입 (header/footer)'
AFTER menu_id;

-- 2. 복합 인덱스 추가 (menu_id + menu_table)
ALTER TABLE menu_page_upload 
ADD INDEX idx_menu_upload (menu_id, menu_table);

-- 확인
SELECT '✅ menu_page_upload 테이블에 menu_table 컬럼 추가 완료!' as result;
