<?php include __DIR__ . '/../_admin_header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/../_sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <h2 class="mb-4">
                <i class="fas fa-fire me-2"></i>트렌드포스팅 설정
            </h2>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">트렌드포스팅 환경설정</h5>
                </div>
                <div class="card-body">
                    <form id="trendpostingForm">
                        <div class="mb-3">
                            <label class="form-label d-block mb-2">트렌드포스팅 사용</label>
                            <div class="custom-radio-wrapper custom-radio-horizontal">
                                <input type="radio" name="enabled" id="enabled_y" value="Y" 
                                    <?php echo (!isset($settings['enabled']) || $settings['enabled'] === 'Y') ? 'checked' : ''; ?>>
                                <label for="enabled_y">사용</label>
                                
                                <input type="radio" name="enabled" id="enabled_n" value="N" 
                                    <?php echo (isset($settings['enabled']) && $settings['enabled'] === 'N') ? 'checked' : ''; ?>>
                                <label for="enabled_n">사용 안함</label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sources" class="form-label">트렌드 소스</label>
                            <textarea class="form-control" id="sources" name="sources" rows="3" 
                                      placeholder="트렌드를 수집할 사이트 URL (한 줄에 하나씩)"><?php echo $settings['sources'] ?? ''; ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="update_interval" class="form-label">업데이트 간격 (분)</label>
                            <input type="number" class="form-control" id="update_interval" name="update_interval" 
                                   value="<?php echo $settings['update_interval'] ?? 120; ?>" min="30">
                        </div>
                        
                        <div class="mb-3">
                            <label for="target_board" class="form-label">대상 게시판</label>
                            <select class="form-select" id="target_board" name="target_board">
                                <option value="">게시판 선택</option>
                                <?php foreach ($boards as $board): ?>
                                    <option value="<?php echo $board['bbs_id']; ?>" 
                                        <?php echo (isset($settings['target_board']) && $settings['target_board'] === $board['bbs_id']) ? 'selected' : ''; ?>>
                                        <?php echo xssFilter($board['bbs_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="min_trend_score" class="form-label">최소 트렌드 점수</label>
                            <input type="number" class="form-control" id="min_trend_score" name="min_trend_score" 
                                   value="<?php echo $settings['min_trend_score'] ?? 50; ?>" min="0" max="100">
                            <small class="text-muted">0-100 사이 값 (높을수록 더 인기있는 트렌드만 선택)</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>설정 저장
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$('#trendpostingForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '/admin/plugin/trendposting',
        method: 'POST',
        data: {
            enabled: $('input[name="enabled"]:checked').val(),
            sources: $('#sources').val(),
            update_interval: $('#update_interval').val(),
            target_board: $('#target_board').val(),
            min_trend_score: $('#min_trend_score').val()
        },
        success: function(response) {
            if (response.success) alert(response.message);
        }
    });
});
</script>

<?php include __DIR__ . '/../../_footer.php'; ?>
