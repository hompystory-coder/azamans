<?php
/**
 * Admin Level & Point Functions
 * 관리자 레벨/포인트 관리 기능
 */

if (!defined('BASE_PATH')) exit('No direct script access allowed');

function admin_levels_handler($controller) {
    $levels = getDbArray("SELECT * FROM level ORDER BY level ASC");
    $data = ['title' => '레벨 관리', 'levels' => $levels];
    $controller->renderView('admin/levels', $data);
}

function admin_points_handler($controller) {
    $data = ['title' => '포인트 관리'];
    $controller->renderView('admin/points', $data);
}
