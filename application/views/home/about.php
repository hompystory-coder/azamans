<?php include __DIR__ . '/../_header.php'; ?>

<main>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm animate__animated animate__fadeIn">
                    <div class="card-header bg-white border-0 py-4">
                        <h1 class="mb-0 fw-bold">
                            <i class="fas fa-info-circle text-main me-2"></i>
                            <?php echo xssFilter(getConfig('about_title', '소개')); ?>
                        </h1>
                    </div>
                    <div class="card-body p-4">
                        <div class="about-content">
                            <?php 
                            $content = getConfig('about_content', '이 사이트에 대한 소개입니다.');
                            echo nl2br(xssFilter($content)); 
                            ?>
                        </div>
                        
                        <?php if (!getConfig('about_content')): ?>
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>
                                관리자 페이지에서 소개 내용을 작성할 수 있습니다.
                                <?php if (isAdmin()): ?>
                                    <a href="/admin/config" class="alert-link ms-2">
                                        <i class="fas fa-arrow-right me-1"></i>설정 페이지로 이동
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0 py-3">
                        <a href="/" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>홈으로 돌아가기
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.about-content {
    font-size: 1rem;
    line-height: 1.8;
    color: #555;
}

.about-content p {
    margin-bottom: 1rem;
}
</style>

<?php include __DIR__ . '/../_footer.php'; ?>
