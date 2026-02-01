<footer class="bg-light border-top mt-5">
    <div class="container py-4">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">&copy; <?php echo date('Y'); ?> <?php echo getConfig('site_name', 'MVC Framework'); ?>. All rights reserved.</p>
                <p class="text-muted small mb-0">Powered by PHP MVC Framework</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-links">
                    <a href="/policy/terms" class="text-decoration-none me-3">이용약관</a>
                    <a href="/policy/privacy" class="text-decoration-none me-3">개인정보보호정책</a>
                    <a href="/policy/youth" class="text-decoration-none">청소년보호정책</a>
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
