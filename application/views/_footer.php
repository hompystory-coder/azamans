<?php
// 푸터 메뉴 조회
$footerMenus = getDbArray("
    SELECT * FROM footer_menu 
    WHERE is_active = 'Y' 
    AND parent_id = 0
    ORDER BY menu_order ASC, id ASC
");
?>
<footer class="bg-light border-top mt-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">&copy; <?php echo date('Y'); ?> <?php echo getConfig('site_name', 'MVC Framework'); ?>. All rights reserved.</p>
                <p class="text-muted small mb-0">Powered by PHP MVC Framework</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-links">
                    <?php if (!empty($footerMenus)): ?>
                        <?php foreach ($footerMenus as $index => $footerMenu): ?>
                            <?php
                            // 메뉴 타입에 따른 URL 생성
                            $menuUrl = '#';
                            $menuTarget = ($footerMenu['target_window'] ?? 'self') === 'blank' ? ' target="_blank"' : '';
                            
                            switch ($footerMenu['menu_type']) {
                                case 'page':
                                    $menuUrl = '/page/footer/' . $footerMenu['id'];
                                    break;
                                case 'board':
                                    if (!empty($footerMenu['menu_target'])) {
                                        $menuUrl = '/bbs/' . xssFilter($footerMenu['menu_target']);
                                    }
                                    break;
                                case 'news':
                                    if (!empty($footerMenu['menu_target'])) {
                                        $menuUrl = '/news/' . xssFilter($footerMenu['menu_target']);
                                    }
                                    break;
                                case 'content':
                                    if (!empty($footerMenu['menu_target'])) {
                                        $menuUrl = '/content/' . xssFilter($footerMenu['menu_target']);
                                    }
                                    break;
                            }
                            
                            // custom_url이 있으면 우선 사용
                            if (!empty($footerMenu['custom_url'])) {
                                $menuUrl = xssFilter($footerMenu['custom_url']);
                            }
                            ?>
                            <a href="<?php echo $menuUrl; ?>" 
                               class="text-decoration-none<?php echo ($index < count($footerMenus) - 1) ? ' me-3' : ''; ?>"
                               <?php echo $menuTarget; ?>>
                                <?php echo xssFilter($footerMenu['menu_name']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- 푸터 메뉴가 없을 때 기본 메시지 또는 비어 있음 -->
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 'Y'): ?>
                            <a href="/admin/menu/footer" class="text-muted text-decoration-none">
                                <i class="bi bi-plus-circle"></i> 푸터 메뉴 생성하기
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
footer {
    margin-top: auto;
}

footer .footer-links a {
    color: #333;
    font-size: 14px;
    transition: color 0.2s;
}

footer .footer-links a:hover {
    color: #ffa50f;
}

@media (max-width: 767px) {
    footer .footer-links {
        margin-top: 15px;
        text-align: left !important;
    }
    
    footer .footer-links a {
        display: inline-block;
        margin-bottom: 5px;
    }
}
</style>
