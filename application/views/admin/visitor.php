<?php include __DIR__ . '/_admin_header.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-chart-line text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="daily" <?php echo $type == 'daily' ? 'selected' : ''; ?>>일별</option>
                            <option value="monthly" <?php echo $type == 'monthly' ? 'selected' : ''; ?>>월별</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>조회</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <canvas id="visitorChart" height="100"></canvas>
            </div>
        </div>
    </main>
</div>
<script>
const labels = <?php echo json_encode(array_column($stats, $type == 'daily' ? 'date' : 'month')); ?>;
const data = <?php echo json_encode(array_column($stats, 'count')); ?>;
new Chart(document.getElementById('visitorChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: '방문자 수',
            data: data,
            borderColor: 'rgb(255, 165, 15)',
            backgroundColor: 'rgba(255, 165, 15, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>
</body></html>
