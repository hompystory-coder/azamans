-- 헤더/푸터 메뉴에 'news' 타입 추가
-- 2026-02-16

-- 1. header_menu에 news 타입 추가
ALTER TABLE `header_menu` 
MODIFY COLUMN `menu_type` enum('page','board','news','content') DEFAULT 'page'
COMMENT '메뉴 타입: page(페이지), board(게시판), news(뉴스), content(콘텐츠)';

-- 2. footer_menu에 news 타입 추가
ALTER TABLE `footer_menu` 
MODIFY COLUMN `menu_type` enum('page','board','news','content') DEFAULT 'page'
COMMENT '메뉴 타입: page(페이지), board(게시판), news(뉴스), content(콘텐츠)';

SELECT '✅ 메뉴 타입에 news 추가 완료!' as result;
