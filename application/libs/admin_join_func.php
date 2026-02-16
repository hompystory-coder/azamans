<?php
/**
 * Admin Join Config Functions
 * 관리자 회원가입 설정 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

function admin_joinconfig_handler($controller, $action = '') {
    if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        setConfig('join_enabled', cleanInput($_POST['join_enabled'] ?? 'Y'));
        setConfig('join_email_verify', cleanInput($_POST['join_email_verify'] ?? 'N'));
        setConfig('join_terms', $_POST['join_terms'] ?? '');
        setConfig('join_privacy', $_POST['join_privacy'] ?? '');
        $controller->renderJson(['success' => true, 'message' => '설정이 저장되었습니다.']);
        return;
    }
    $data = ['title' => '회원가입 설정'];
    $controller->renderView('admin/joinconfig', $data);
}
