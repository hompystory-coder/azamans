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
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../_footer.php'; ?>
