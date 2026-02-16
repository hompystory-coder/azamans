<?php
/**
 * Admin Config Functions
 * 관리자 사이트 설정 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 사이트 설정 핸들러
 * @param object $controller Admin controller instance
 * @param string $action Action name
 */
function admin_config_handler($controller, $action = '') {
    // 하위 메서드 처리
    if ($action === 'uploadLogo') {
        admin_config_upload_logo($controller);
        return;
    } elseif ($action === 'deleteLogo') {
        admin_config_delete_logo($controller);
        return;
    } elseif ($action === 'saveDimensions') {
        admin_config_save_dimensions($controller);
        return;
    } elseif ($action === 'saveBasic') {
        admin_config_save_basic($controller);
        return;
    } elseif ($action === 'saveImageSettings') {
        admin_config_save_image_settings($controller);
        return;
    } elseif ($action === 'saveWatermarkSettings') {
        admin_config_save_watermark_settings($controller);
        return;
    } elseif ($action === 'uploadWatermark') {
        admin_config_upload_watermark($controller);
        return;
    } elseif ($action === 'deleteWatermark') {
        admin_config_delete_watermark($controller);
        return;
    }
    
    // 모든 설정 로드 (admin_config와 site_config 모두)
    $adminConfigRows = getDbArray("SELECT config_key, config_value FROM admin_config");
    $siteConfigRows = getDbArray("SELECT config_key, config_value FROM site_config");
    
    $configs = [];
    foreach ($adminConfigRows as $row) {
        $configs[$row['config_key']] = $row['config_value'];
    }
    foreach ($siteConfigRows as $row) {
        $configs[$row['config_key']] = $row['config_value'];
    }
    
    $data = [
        'title' => '사이트 설정',
        'configs' => $configs
    ];
    
    $controller->renderView('admin/config', $data);
}

/**
 * 기본 설정 저장
 */
function admin_config_save_basic($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $configs = [
        'site_name' => cleanInput($_POST['site_name'] ?? ''),
        'site_url' => cleanInput($_POST['site_url'] ?? ''),
        'site_email' => cleanInput($_POST['site_email'] ?? '')
    ];
    
    // site_config 테이블에 저장
    foreach ($configs as $key => $value) {
        $exists = getUidData("SELECT config_key FROM site_config WHERE config_key = ?", [$key]);
        
        if ($exists) {
            getDbUpdate('site_config', 
                ['config_value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                'config_key = ?',
                [$key]
            );
        } else {
            getDbInsert('site_config', [
                'config_key' => $key,
                'config_value' => $value,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    $controller->renderJson(['success' => true, 'message' => '설정이 저장되었습니다.']);
}

/**
 * 로고 업로드
 */
function admin_config_upload_logo($controller) {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = '파일 업로드 오류: ';
            if (isset($_FILES['logo']['error'])) {
                $errorMsg .= 'Error code ' . $_FILES['logo']['error'];
            }
            $controller->renderJson(['success' => false, 'message' => $errorMsg], 400);
            return;
        }
        
        $logoType = cleanInput($_POST['logo_type'] ?? '');
        $width = (int)($_POST['width'] ?? 0);
        $height = (int)($_POST['height'] ?? 0);
        
        if (empty($logoType)) {
            $controller->renderJson(['success' => false, 'message' => '로고 타입이 지정되지 않았습니다.'], 400);
            return;
        }
        
        $file = $_FILES['logo'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed)) {
            $controller->renderJson(['success' => false, 'message' => '이미지 파일만 업로드 가능합니다. (jpg, png, gif, webp)'], 400);
            return;
        }
        
        // 업로드 디렉토리
        $uploadDir = BASE_PATH . '/public/uploads/logos';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $controller->renderJson(['success' => false, 'message' => '업로드 디렉토리 생성 실패'], 500);
                return;
            }
        }
        
        // 파일명 생성
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $logoType . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $url = '/public/uploads/logos/' . $filename;
            
            // site_config 테이블에 저장 (admin_config가 아닌)
            $configs = [
                $logoType => $url,
                $logoType . '_width' => $width,
                $logoType . '_height' => $height
            ];
            
            foreach ($configs as $key => $value) {
                $pdo = getDBConnection();
                $exists = getUidData("SELECT config_key FROM site_config WHERE config_key = ?", [$key]);
                
                if ($exists) {
                    $sql = "UPDATE site_config SET config_value = ?, updated_at = NOW() WHERE config_key = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$value, $key]);
                } else {
                    $sql = "INSERT INTO site_config (config_key, config_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$key, $value]);
                }
            }
            
            $controller->renderJson(['success' => true, 'url' => $url, 'message' => '로고가 업로드되었습니다.']);
        } else {
            $controller->renderJson(['success' => false, 'message' => '파일 저장에 실패했습니다.'], 500);
        }
    } catch (Exception $e) {
        $controller->renderJson(['success' => false, 'message' => '업로드 중 오류: ' . $e->getMessage()], 500);
    }
}

/**
 * 로고 크기 저장
 */
function admin_config_save_dimensions($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false], 400);
        return;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $logoType = cleanInput($input['logo_type'] ?? '');
    $width = (int)($input['width'] ?? 0);
    $height = (int)($input['height'] ?? 0);
    
    if (empty($logoType)) {
        $controller->renderJson(['success' => false], 400);
        return;
    }
    
    setConfig($logoType . '_width', $width);
    setConfig($logoType . '_height', $height);
    
    $controller->renderJson(['success' => true]);
}

/**
 * 로고 삭제
 */
function admin_config_delete_logo($controller) {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
            return;
        }
        
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $controller->renderJson(['success' => false, 'message' => 'JSON 파싱 오류: ' . json_last_error_msg()], 400);
            return;
        }
        
        $logoType = cleanInput($input['logo_type'] ?? '');
        
        if (empty($logoType)) {
            $controller->renderJson(['success' => false, 'message' => '로고 타입이 지정되지 않았습니다.'], 400);
            return;
        }
        
        // site_config에서 파일 경로 가져오기
        $logoData = getUidData("SELECT config_value FROM site_config WHERE config_key = ?", [$logoType]);
        $logoUrl = $logoData['config_value'] ?? '';
        
        // 파일 삭제
        $fileDeleted = false;
        $filepath = '';
        if ($logoUrl) {
            $filepath = BASE_PATH . '/' . ltrim($logoUrl, '/');
            if (file_exists($filepath)) {
                $fileDeleted = @unlink($filepath);
            }
        }
        
        // site_config에서 삭제 (값을 빈 문자열로)
        $keys = [$logoType, $logoType . '_width', $logoType . '_height'];
        $dbUpdated = 0;
        foreach ($keys as $key) {
            $sql = "UPDATE site_config SET config_value = '', updated_at = NOW() WHERE config_key = ?";
            $stmt = getDBConnection()->prepare($sql);
            if ($stmt->execute([$key])) {
                $dbUpdated += $stmt->rowCount();
            }
        }
        
        $controller->renderJson([
            'success' => true, 
            'message' => '로고가 삭제되었습니다.'
        ]);
    } catch (Exception $e) {
        $controller->renderJson(['success' => false, 'message' => '삭제 중 오류: ' . $e->getMessage()], 500);
    }
}

/**
 * 이미지 설정 저장
 */
function admin_config_save_image_settings($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $settings = [
        'image_max_width' => (int)($_POST['image_max_width'] ?? 900),
        'image_quality' => (int)($_POST['image_quality'] ?? 100),
        'thumb_big_width' => (int)($_POST['thumb_big_width'] ?? 900),
        'thumb_big_height' => (int)($_POST['thumb_big_height'] ?? 600),
        'thumb_middle_width' => (int)($_POST['thumb_middle_width'] ?? 640),
        'thumb_middle_height' => (int)($_POST['thumb_middle_height'] ?? 480),
        'thumb_small_width' => (int)($_POST['thumb_small_width'] ?? 480),
        'thumb_small_height' => (int)($_POST['thumb_small_height'] ?? 360),
        'thumb_quality' => (int)($_POST['thumb_quality'] ?? 100),
        'thumbnail_delete_original' => cleanInput($_POST['thumbnail_delete_original'] ?? 'N'),
        'thumbnail_transparent_bg' => cleanInput($_POST['thumbnail_transparent_bg'] ?? 'white')
    ];
    
    // 유효성 검사
    if ($settings['image_quality'] < 1 || $settings['image_quality'] > 100) {
        $controller->renderJson(['success' => false, 'message' => '이미지 품질은 1~100 사이여야 합니다.'], 400);
        return;
    }
    
    if ($settings['thumb_quality'] < 1 || $settings['thumb_quality'] > 100) {
        $controller->renderJson(['success' => false, 'message' => '썸네일 해상도는 1~100 사이여야 합니다.'], 400);
        return;
    }
    
    if (!in_array($settings['thumbnail_delete_original'], ['Y', 'N'])) {
        $settings['thumbnail_delete_original'] = 'N';
    }
    
    if (!in_array($settings['thumbnail_transparent_bg'], ['white', 'black'])) {
        $settings['thumbnail_transparent_bg'] = 'white';
    }
    
    // site_config 테이블에 저장
    foreach ($settings as $key => $value) {
        getDbUpdate('site_config', ['config_value' => $value], 'config_key = ?', [$key]);
    }
    
    // var 파일로 저장
    admin_config_save_to_var('image', $settings);
    
    $controller->renderJson(['success' => true, 'message' => '이미지 설정이 저장되었습니다.']);
}

/**
 * 워터마크 설정 저장
 */
function admin_config_save_watermark_settings($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $settings = [
        'watermark_enabled' => cleanInput($_POST['watermark_enabled'] ?? 'N'),
        'watermark_target_board' => cleanInput($_POST['watermark_target_board'] ?? 'Y'),
        'watermark_target_page' => cleanInput($_POST['watermark_target_page'] ?? 'Y'),
        'watermark_position' => (int)($_POST['watermark_position'] ?? 5),
        'watermark_opacity' => (int)($_POST['watermark_opacity'] ?? 80)
    ];
    
    // 유효성 검사
    if (!in_array($settings['watermark_enabled'], ['Y', 'N'])) {
        $settings['watermark_enabled'] = 'N';
    }
    
    if (!in_array($settings['watermark_target_board'], ['Y', 'N'])) {
        $settings['watermark_target_board'] = 'Y';
    }
    
    if (!in_array($settings['watermark_target_page'], ['Y', 'N'])) {
        $settings['watermark_target_page'] = 'Y';
    }
    
    if ($settings['watermark_position'] < 1 || $settings['watermark_position'] > 5) {
        $settings['watermark_position'] = 5;
    }
    
    if ($settings['watermark_opacity'] < 0 || $settings['watermark_opacity'] > 100) {
        $settings['watermark_opacity'] = 80;
    }
    
    // site_config 테이블에 저장
    foreach ($settings as $key => $value) {
        getDbUpdate('site_config', ['config_value' => $value], 'config_key = ?', [$key]);
    }
    
    $controller->renderJson(['success' => true, 'message' => '워터마크 설정이 저장되었습니다.']);
}

/**
 * 워터마크 이미지 업로드
 */
function admin_config_upload_watermark($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    if (!isset($_FILES['watermark'])) {
        $controller->renderJson(['success' => false, 'message' => '파일이 전송되지 않았습니다.'], 400);
        return;
    }
    
    if ($_FILES['watermark']['error'] !== UPLOAD_ERR_OK) {
        $controller->renderJson(['success' => false, 'message' => '파일 업로드 오류가 발생했습니다.'], 400);
        return;
    }
    
    $file = $_FILES['watermark'];
    
    // PNG 파일만 허용
    if ($file['type'] !== 'image/png') {
        $controller->renderJson(['success' => false, 'message' => 'PNG 파일만 업로드 가능합니다.'], 400);
        return;
    }
    
    // 기존 워터마크 삭제
    $oldWatermark = getUidData("SELECT config_value FROM site_config WHERE config_key = 'watermark_image'", [])['config_value'] ?? '';
    if ($oldWatermark) {
        $oldPath = BASE_PATH . '/' . ltrim($oldWatermark, '/');
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }
    
    // 업로드 디렉토리
    $uploadDir = BASE_PATH . '/public/uploads/watermark';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // 파일명 생성
    $filename = 'watermark_' . time() . '.png';
    $filepath = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $url = '/public/uploads/watermark/' . $filename;
        
        // DB에 저장
        getDbUpdate('site_config', ['config_value' => $url], 'config_key = ?', ['watermark_image']);
        
        $controller->renderJson(['success' => true, 'url' => $url, 'message' => '워터마크가 업로드되었습니다.']);
    } else {
        $controller->renderJson(['success' => false, 'message' => '파일 저장에 실패했습니다.'], 500);
    }
}

/**
 * 워터마크 삭제
 */
function admin_config_delete_watermark($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    $watermark = getUidData("SELECT config_value FROM site_config WHERE config_key = 'watermark_image'", [])['config_value'] ?? '';
    
    if ($watermark) {
        $filepath = BASE_PATH . '/' . ltrim($watermark, '/');
        if (file_exists($filepath)) {
            @unlink($filepath);
        }
    }
    
    // DB에서 삭제
    getDbUpdate('site_config', ['config_value' => ''], 'config_key = ?', ['watermark_image']);
    
    $controller->renderJson(['success' => true, 'message' => '워터마크가 삭제되었습니다.']);
}

/**
 * 설정을 var 파일로 저장
 */
function admin_config_save_to_var($filename, $settings) {
    $varDir = APP_PATH . '/config/var';
    if (!is_dir($varDir)) {
        mkdir($varDir, 0755, true);
    }
    
    $varFile = $varDir . '/' . $filename . '.var.php';
    $content = "<?php\n// Auto-generated config file\n";
    $content .= "// Generated at: " . date('Y-m-d H:i:s') . "\n\n";
    $content .= "return " . var_export($settings, true) . ";\n";
    
    file_put_contents($varFile, $content);
    @chmod($varFile, 0644);
}

/**
 * 설정 저장 핸들러 (일반 설정)
 */
function admin_config_save_handler($controller) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->renderJson(['success' => false, 'message' => '잘못된 요청입니다.'], 400);
        return;
    }
    
    // 설정 항목들
    $configs = [
        'site_name' => cleanInput($controller->postParam('site_name')),
        'site_url' => cleanInput($controller->postParam('site_url')),
        'site_email' => cleanInput($controller->postParam('site_email')),
        'about_title' => cleanInput($controller->postParam('about_title')),
        'about_content' => $controller->postParam('about_content') // HTML 허용
    ];
    
    // 각 설정을 DB에 저장
    foreach ($configs as $key => $value) {
        // 설정이 이미 존재하는지 확인
        $existing = getUidData("SELECT uid FROM admin_config WHERE config_key = ?", [$key]);
        
        if ($existing) {
            // 업데이트
            getDbUpdate('admin_config', 
                ['config_value' => $value], 
                'config_key = ?', 
                [$key]
            );
        } else {
            // 신규 삽입
            getDbInsert('admin_config', [
                'config_key' => $key,
                'config_value' => $value,
                'config_group' => 'general'
            ]);
        }
    }
    
    $controller->renderJson([
        'success' => true,
        'message' => '설정이 저장되었습니다.'
    ]);
}

/**
 * 파비콘 관리 핸들러
 */
function admin_favicon_handler($controller, $action = null) {
    // 파비콘 업로드
    if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            if (!isset($_FILES['favicon']) || $_FILES['favicon']['error'] !== UPLOAD_ERR_OK) {
                $errorCode = $_FILES['favicon']['error'] ?? 'NO_FILE';
                $controller->renderJson(['success' => false, 'message' => '파일 업로드에 실패했습니다. 에러 코드: ' . $errorCode], 400);
                return;
            }
            
            $file = $_FILES['favicon'];
            $allowedTypes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $file['tmp_name']);
            finfo_close($fileInfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $controller->renderJson(['success' => false, 'message' => '지원하지 않는 파일 형식입니다. (' . $mimeType . ')'], 400);
                return;
            }
            
            $uploadDir = BASE_PATH . '/public/uploads/favicon/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // 기존 파비콘 삭제
            $oldFaviconData = getUidData("SELECT config_value FROM site_config WHERE config_key = 'favicon_url'", []);
            $oldFavicon = $oldFaviconData['config_value'] ?? '';
            if ($oldFavicon) {
                $oldFile = BASE_PATH . $oldFavicon;
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }
            
            // 파일 확장자 결정
            $extension = 'ico';
            if ($mimeType === 'image/png') $extension = 'png';
            elseif ($mimeType === 'image/jpeg') $extension = 'jpg';
            elseif ($mimeType === 'image/gif') $extension = 'gif';
            
            $newFileName = 'favicon_' . time() . '.' . $extension;
            $newFilePath = $uploadDir . $newFileName;
            
            if (move_uploaded_file($file['tmp_name'], $newFilePath)) {
                $faviconUrl = '/public/uploads/favicon/' . $newFileName;
                
                // site_config 테이블에 저장
                $exists = getUidData("SELECT config_key FROM site_config WHERE config_key = 'favicon_url'", []);
                if ($exists) {
                    getDbUpdate('site_config',
                        ['config_value' => $faviconUrl, 'updated_at' => date('Y-m-d H:i:s')],
                        'config_key = ?',
                        ['favicon_url']
                    );
                } else {
                    getDbInsert('site_config', [
                        'config_key' => 'favicon_url',
                        'config_value' => $faviconUrl,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                
                $controller->renderJson(['success' => true, 'message' => '파비콘이 업로드되었습니다.', 'url' => $faviconUrl]);
            } else {
                $controller->renderJson(['success' => false, 'message' => '파일 저장에 실패했습니다. 경로: ' . $newFilePath], 500);
            }
        } catch (Exception $e) {
            $controller->renderJson(['success' => false, 'message' => '에러 발생: ' . $e->getMessage()], 500);
        }
        return;
    }
    
    // 파비콘 삭제
    if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $faviconData = getUidData("SELECT config_value FROM site_config WHERE config_key = 'favicon_url'", []);
        $faviconUrl = $faviconData['config_value'] ?? '';
        
        if ($faviconUrl) {
            $filePath = BASE_PATH . $faviconUrl;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            
            // site_config에서 삭제
            getDbUpdate('site_config',
                ['config_value' => '', 'updated_at' => date('Y-m-d H:i:s')],
                'config_key = ?',
                ['favicon_url']
            );
            
            $controller->renderJson(['success' => true, 'message' => '파비콘이 삭제되었습니다.']);
        } else {
            $controller->renderJson(['success' => false, 'message' => '삭제할 파비콘이 없습니다.'], 404);
        }
        return;
    }
    
    // 파비콘 설정 페이지
    $faviconData = getUidData("SELECT config_value FROM site_config WHERE config_key = 'favicon_url'", []);
    $data = [
        'title' => '파비콘 설정',
        'favicon_url' => $faviconData['config_value'] ?? ''
    ];
    $controller->renderView('admin/favicon', $data);
}

/**
 * 헤더 코드 관리 핸들러
 */
function admin_headercode_handler($controller) {
    $data = [
        'title' => '헤더 코드',
        'header_code' => getConfig('header_code', '')
    ];
    $controller->renderView('admin/headercode', $data);
}

/**
 * 푸터 코드 관리 핸들러
 */
function admin_footercode_handler($controller) {
    $data = [
        'title' => '푸터 코드',
        'footer_code' => getConfig('footer_code', '')
    ];
    $controller->renderView('admin/footercode', $data);
}
