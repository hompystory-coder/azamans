<?php include __DIR__ . '/../_admin_header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/../_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <h2 class="mb-4">
                <i class="fas fa-robot me-2"></i>자동포스팅 설정
            </h2>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-cog me-2"></i>자동포스팅 환경설정
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="autopostForm">
                                <!-- 사용 여부 -->
                                <div class="mb-3">
                                    <label class="form-label d-block mb-2">자동포스팅 사용</label>
                                    <div class="custom-radio-wrapper custom-radio-horizontal">
                                        <input type="radio" name="enabled" id="enabled_y" value="Y" <?php echo (!isset($settings['enabled']) || $settings['enabled'] === 'Y') ? 'checked' : ''; ?>>
                                        <label for="enabled_y">사용</label>
                                        
                                        <input type="radio" name="enabled" id="enabled_n" value="N" <?php echo (isset($settings['enabled']) && $settings['enabled'] === 'N') ? 'checked' : ''; ?>>
                                        <label for="enabled_n">사용 안함</label>
                                    </div>
                                </div>
                                
                                <!-- 실행 간격 -->
                                <div class="mb-3">
                                    <label for="interval" class="form-label">실행 간격 (분)</label>
                                    <input type="number" class="form-control" id="interval" name="interval" 
                                           value="<?php echo $settings['interval'] ?? 60; ?>" min="1" max="1440">
                                    <small class="text-muted">자동포스팅 실행 간격을 분 단위로 설정합니다.</small>
                                </div>
                                
                                <!-- 대상 게시판 -->
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
                                
                                <!-- 키워드 -->
                                <div class="mb-3">
                                    <label for="keywords" class="form-label">검색 키워드</label>
                                    <textarea class="form-control" id="keywords" name="keywords" rows="3"
                                              placeholder="콤마(,)로 구분하여 여러 키워드 입력"><?php echo $settings['keywords'] ?? ''; ?></textarea>
                                    <small class="text-muted">예: IT뉴스, 기술동향, 프로그래밍</small>
                                </div>
                                
                                <!-- 자동 발행 -->
                                <div class="mb-3">
                                    <label class="form-label d-block mb-2">자동 발행</label>
                                    <div class="custom-radio-wrapper custom-radio-horizontal">
                                        <input type="radio" name="auto_publish" id="auto_publish_y" value="Y" <?php echo (isset($settings['auto_publish']) && $settings['auto_publish'] === 'Y') ? 'checked' : ''; ?>>
                                        <label for="auto_publish_y">즉시 발행</label>
                                        
                                        <input type="radio" name="auto_publish" id="auto_publish_n" value="N" <?php echo (!isset($settings['auto_publish']) || $settings['auto_publish'] === 'N') ? 'checked' : ''; ?>>
                                        <label for="auto_publish_n">임시 저장</label>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>설정 저장
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>안내
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>자동포스팅이란?</h6>
                            <p class="small">설정된 키워드를 기반으로 자동으로 콘텐츠를 수집하고 게시판에 포스팅하는 기능입니다.</p>
                            
                            <h6 class="mt-3">사용 방법</h6>
                            <ol class="small">
                                <li>자동포스팅 기능을 활성화합니다.</li>
                                <li>실행 간격을 설정합니다.</li>
                                <li>대상 게시판을 선택합니다.</li>
                                <li>검색할 키워드를 입력합니다.</li>
                                <li>설정을 저장합니다.</li>
                            </ol>
                            
                            <div class="alert alert-warning mt-3 small">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                자동포스팅은 서버 리소스를 사용하므로 적절한 간격으로 설정해주세요.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#autopostForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            enabled: $('input[name="enabled"]:checked').val(),
            interval: $('#interval').val(),
            target_board: $('#target_board').val(),
            keywords: $('#keywords').val(),
            auto_publish: $('input[name="auto_publish"]:checked').val()
        };
        
        $.ajax({
            url: '/admin/plugin/autopost',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                } else {
                    alert('오류: ' + response.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                alert('설정 저장 중 오류가 발생했습니다.');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../../_footer.php'; ?>
