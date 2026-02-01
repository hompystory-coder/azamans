<?php include __DIR__ . '/../_header.php'; ?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/">홈</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= xssFilter($title) ?></li>
                </ol>
            </nav>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-shield-alt me-2"></i><?= xssFilter($title) ?>
                    </h1>
                </div>
                <div class="card-body">
                    <div class="policy-content">
                        <?php if (!empty($content)): ?>
                            <?= nl2br(xssFilter($content)) ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                아직 개인정보보호정책이 등록되지 않았습니다.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="text-center">
                        <a href="/" class="btn btn-secondary">
                            <i class="fas fa-home me-2"></i>홈으로
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.policy-content {
    line-height: 1.8;
    font-size: 15px;
    white-space: pre-wrap;
}

.breadcrumb {
    background: none;
    padding: 0;
    margin-bottom: 20px;
}

.breadcrumb a {
    color: #ffa50f;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.card-header.bg-primary {
    background-color: #ffa50f !important;
}
</style>

<?php include __DIR__ . '/../_footer.php'; ?>
