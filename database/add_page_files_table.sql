-- 페이지 첨부파일 테이블
CREATE TABLE IF NOT EXISTS page_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL COMMENT '메뉴 ID (header_menu.id)',
    original_name VARCHAR(255) NOT NULL COMMENT '원본 파일명',
    saved_name VARCHAR(255) NOT NULL COMMENT '저장된 파일명',
    file_path VARCHAR(500) NOT NULL COMMENT '파일 경로',
    file_size BIGINT DEFAULT 0 COMMENT '파일 크기 (bytes)',
    file_type VARCHAR(100) NULL COMMENT '파일 타입 (MIME)',
    download_count INT DEFAULT 0 COMMENT '다운로드 횟수',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_menu_id (menu_id),
    FOREIGN KEY (menu_id) REFERENCES header_menu(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
