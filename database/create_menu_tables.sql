-- header_menu 테이블 (기존 삭제 후 재생성)
DROP TABLE IF EXISTS header_menu;
CREATE TABLE header_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT 0 COMMENT '상위 메뉴 ID (0=최상위)',
    menu_name VARCHAR(100) NOT NULL COMMENT '메뉴명',
    menu_type ENUM('page', 'board', 'content') DEFAULT 'page' COMMENT '메뉴 타입',
    menu_target VARCHAR(255) NULL COMMENT '연결 대상 (게시판ID, 콘텐츠ID 등)',
    custom_url VARCHAR(500) NULL COMMENT '커스텀 URL',
    use_redirect ENUM('Y', 'N') DEFAULT 'N' COMMENT '리다이렉트 사용',
    target_window ENUM('self', 'blank') DEFAULT 'self' COMMENT '창 열기 방식',
    is_hidden ENUM('Y', 'N') DEFAULT 'N' COMMENT '숨김',
    is_blocked ENUM('Y', 'N') DEFAULT 'N' COMMENT '차단',
    menu_order INT DEFAULT 0 COMMENT '정렬 순서',
    is_active ENUM('Y', 'N') DEFAULT 'Y' COMMENT '활성화',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parent (parent_id),
    INDEX idx_order (menu_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- footer_menu 테이블
DROP TABLE IF EXISTS footer_menu;
CREATE TABLE footer_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT 0,
    menu_name VARCHAR(100) NOT NULL,
    menu_type ENUM('page', 'board', 'content') DEFAULT 'page',
    menu_target VARCHAR(255) NULL,
    custom_url VARCHAR(500) NULL,
    use_redirect ENUM('Y', 'N') DEFAULT 'N',
    target_window ENUM('self', 'blank') DEFAULT 'self',
    is_hidden ENUM('Y', 'N') DEFAULT 'N',
    is_blocked ENUM('Y', 'N') DEFAULT 'N',
    menu_order INT DEFAULT 0,
    is_active ENUM('Y', 'N') DEFAULT 'Y',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parent (parent_id),
    INDEX idx_order (menu_order),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- menu_pages 테이블 (페이지 타입 콘텐츠 저장)
DROP TABLE IF EXISTS menu_pages;
CREATE TABLE menu_pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_id INT NOT NULL COMMENT 'header_menu.id',
    content TEXT NULL COMMENT '페이지 내용',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_menu_id (menu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
