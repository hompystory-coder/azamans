<?php include __DIR__ . '/../_header.php'; ?>

<main>
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h3 mb-0"><?php echo xssFilter($menu['menu_name']); ?></h1>
            </div>
            <div class="card-body">
                <div class="page-content">
                    <?php echo $content; ?>
                </div>
                
                <!-- 첨부파일 목록 -->
                <?php if (!empty($pageFiles)): ?>
                <div class="attachments mt-4 pt-4 border-top">
                    <h5 class="mb-3">
                        <i class="fas fa-paperclip me-2"></i>첨부파일 (<?php echo count($pageFiles); ?>)
                    </h5>
                    <div class="list-group">
                        <?php foreach ($pageFiles as $file): ?>
                        <a href="/page/download/<?php echo $file['uid']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-file me-2 text-primary"></i>
                                <strong><?php echo xssFilter($file['original_name']); ?></strong>
                                <small class="text-muted ms-2">
                                    (<?php echo number_format($file['filesize'] / 1024, 2); ?> KB)
                                </small>
                            </div>
                            <div>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-download me-1"></i><?php echo number_format($file['download_count']); ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../_footer.php'; ?>
