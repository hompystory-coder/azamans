-- menu_page_upload 테이블 생성 (bbs_upload 구조 참고)
-- 페이지 첨부파일 관리 테이블

DROP TABLE IF EXISTS menu_page_upload;

CREATE TABLE menu_page_upload (
    uid INT AUTO_INCREMENT PRIMARY KEY COMMENT '첨부파일 고유번호',
    menu_id INT NOT NULL COMMENT '메뉴 ID (header_menu.id)',
    filename VARCHAR(255) NOT NULL COMMENT '저장된 파일명',
    original_name VARCHAR(255) NOT NULL COMMENT '원본 파일명',
    filepath VARCHAR(500) NOT NULL COMMENT '파일 경로',
    filesize BIGINT NOT NULL COMMENT '파일 크기 (bytes)',
    mime_type VARCHAR(100) NULL COMMENT 'MIME 타입',
    download_count INT DEFAULT 0 COMMENT '다운로드 횟수',
    reg_date DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    
    INDEX idx_menu_id (menu_id),
    FOREIGN KEY (menu_id) REFERENCES header_menu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메뉴 페이지 첨부파일';
