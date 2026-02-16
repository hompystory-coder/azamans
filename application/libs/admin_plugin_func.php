<?php
/**
 * Admin Plugin Functions
 * 관리자 플러그인 관리 기능
 * 
 * 향후 확장 예정:
 * - AI 자동 글쓰기 플러그인
 * - 이미지 자동 생성 플러그인
 * - SEO 자동 최적화 플러그인
 * - 소셜 미디어 자동 공유 플러그인
 * - 광고 관리 플러그인
 * - 백업/복원 플러그인
 * - 멀티사이트 관리 플러그인
 * - 회원 등급 자동화 플러그인
 * - 댓글 스팸 필터 플러그인
 * - 실시간 알림 플러그인
 * - 결제 시스템 플러그인
 * - 채팅/메신저 플러그인
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

/**
 * 플러그인 메인 라우터
 */
function admin_plugin_handler($adminController, $pluginId = null) {
    if (!$pluginId) {
        redirect('/admin');
        return;
    }
    
    // 플러그인별 분기
    switch ($pluginId) {
        case 'autopost':
            return admin_plugin_autopost($adminController);
        case 'videocreate':
            return admin_plugin_videocreate($adminController);
        case 'trendposting':
            return admin_plugin_trendposting($adminController);
        default:
            redirect('/admin');
    }
}

/**
 * 자동포스팅 플러그인
 * - 키워드 기반 자동 게시글 생성
 * - 일정 주기로 자동 발행
 * - 게시판별 자동 배포
 */
function admin_plugin_autopost($adminController) {
    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $settings = [
            'enabled' => cleanInput($_POST['enabled'] ?? 'N'),
            'interval' => (int)($_POST['interval'] ?? 60),
            'target_board' => cleanInput($_POST['target_board'] ?? ''),
            'keywords' => cleanInput($_POST['keywords'] ?? ''),
            'auto_publish' => cleanInput($_POST['auto_publish'] ?? 'N')
        ];
        
        // 설정 저장 (site_config 테이블에 JSON 형태로 저장)
        $settingsJson = json_encode($settings);
        
        // site_config 테이블에 저장 또는 업데이트
        $existing = getUidData("SELECT * FROM site_config WHERE config_key = 'plugin_autopost'", []);
        
        if ($existing) {
            getDbUpdate('site_config', ['config_value' => $settingsJson], 'config_key = ?', ['plugin_autopost']);
        } else {
            getDbInsert('site_config', [
                'config_key' => 'plugin_autopost',
                'config_value' => $settingsJson
            ]);
        }
        
        $adminController->renderJson(['success' => true, 'message' => '자동포스팅 설정이 저장되었습니다.']);
        return;
    }
    
    // 설정 불러오기
    $settingsData = getUidData("SELECT config_value FROM site_config WHERE config_key = 'plugin_autopost'", []);
    $settings = $settingsData ? json_decode($settingsData['config_value'], true) : [];
    
    // 게시판 목록 조회
    $boards = getDbArray("SELECT * FROM bbs_list ORDER BY bbs_name ASC", []) ?? [];
    
    $data = [
        'title' => '자동포스팅 설정',
        'settings' => $settings,
        'boards' => $boards
    ];
    
    $adminController->renderView('admin/plugin/autopost', $data);
}

/**
 * 동영상 생성 플러그인
 * - AI 기반 동영상 자동 생성
 * - 텍스트-투-비디오 변환
 * - 썸네일 자동 생성
 * - 자동 업로드 및 게시
 */
function admin_plugin_videocreate($adminController) {
    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $settings = [
            'enabled' => cleanInput($_POST['enabled'] ?? 'N'),
            'api_key' => cleanInput($_POST['api_key'] ?? ''),
            'video_quality' => cleanInput($_POST['video_quality'] ?? 'HD'),
            'auto_upload' => cleanInput($_POST['auto_upload'] ?? 'N'),
            'output_path' => cleanInput($_POST['output_path'] ?? '/uploads/videos/')
        ];
        
        $settingsJson = json_encode($settings);
        
        $existing = getUidData("SELECT * FROM site_config WHERE config_key = 'plugin_videocreate'", []);
        
        if ($existing) {
            getDbUpdate('site_config', ['config_value' => $settingsJson], 'config_key = ?', ['plugin_videocreate']);
        } else {
            getDbInsert('site_config', [
                'config_key' => 'plugin_videocreate',
                'config_value' => $settingsJson
            ]);
        }
        
        $adminController->renderJson(['success' => true, 'message' => '동영상생성 설정이 저장되었습니다.']);
        return;
    }
    
    // 설정 불러오기
    $settingsData = getUidData("SELECT config_value FROM site_config WHERE config_key = 'plugin_videocreate'", []);
    $settings = $settingsData ? json_decode($settingsData['config_value'], true) : [];
    
    $data = [
        'title' => '동영상생성 설정',
        'settings' => $settings
    ];
    
    $adminController->renderView('admin/plugin/videocreate', $data);
}

/**
 * 트렌드 포스팅 플러그인
 * - 실시간 트렌드 키워드 수집
 * - 자동 트렌드 분석
 * - 트렌드 기반 자동 게시글 생성
 * - 트렌드 스코어링 시스템
 */
function admin_plugin_trendposting($adminController) {
    // POST 요청 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $settings = [
            'enabled' => cleanInput($_POST['enabled'] ?? 'N'),
            'sources' => cleanInput($_POST['sources'] ?? ''),
            'update_interval' => (int)($_POST['update_interval'] ?? 120),
            'target_board' => cleanInput($_POST['target_board'] ?? ''),
            'min_trend_score' => (int)($_POST['min_trend_score'] ?? 50)
        ];
        
        $settingsJson = json_encode($settings);
        
        $existing = getUidData("SELECT * FROM site_config WHERE config_key = 'plugin_trendposting'", []);
        
        if ($existing) {
            getDbUpdate('site_config', ['config_value' => $settingsJson], 'config_key = ?', ['plugin_trendposting']);
        } else {
            getDbInsert('site_config', [
                'config_key' => 'plugin_trendposting',
                'config_value' => $settingsJson
            ]);
        }
        
        $adminController->renderJson(['success' => true, 'message' => '트렌드포스팅 설정이 저장되었습니다.']);
        return;
    }
    
    // 설정 불러오기
    $settingsData = getUidData("SELECT config_value FROM site_config WHERE config_key = 'plugin_trendposting'", []);
    $settings = $settingsData ? json_decode($settingsData['config_value'], true) : [];
    
    // 게시판 목록 조회
    $boards = getDbArray("SELECT * FROM bbs_list ORDER BY bbs_name ASC", []) ?? [];
    
    $data = [
        'title' => '트렌드포스팅 설정',
        'settings' => $settings,
        'boards' => $boards
    ];
    
    $adminController->renderView('admin/plugin/trendposting', $data);
}
