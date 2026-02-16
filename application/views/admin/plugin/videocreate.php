<?php include __DIR__ . '/../_admin_header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/../_sidebar.php'; ?>
    
    <div class="flex-grow-1 p-4">
        <div class="container-fluid">
            <h2 class="mb-4">
                <i class="fas fa-video me-2"></i>동영상생성 설정
            </h2>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">동영상생성 환경설정</h5>
                </div>
                <div class="card-body">
                    <form id="videocreateForm">
                        <div class="mb-3">
                            <label class="form-label d-block mb-2">동영상생성 사용</label>
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
                            <label for="api_key" class="form-label">API 키</label>
                            <input type="text" class="form-control" id="api_key" name="api_key" 
                                   value="<?php echo $settings['api_key'] ?? ''; ?>" placeholder="API 키 입력">
                        </div>
                        
                        <div class="mb-3">
                            <label for="video_quality" class="form-label">비디오 품질</label>
                            <select class="form-select" id="video_quality" name="video_quality">
                                <option value="SD" <?php echo (isset($settings['video_quality']) && $settings['video_quality'] === 'SD') ? 'selected' : ''; ?>>SD</option>
                                <option value="HD" <?php echo (!isset($settings['video_quality']) || $settings['video_quality'] === 'HD') ? 'selected' : ''; ?>>HD</option>
                                <option value="FHD" <?php echo (isset($settings['video_quality']) && $settings['video_quality'] === 'FHD') ? 'selected' : ''; ?>>Full HD</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="output_path" class="form-label">출력 경로</label>
                            <input type="text" class="form-control" id="output_path" name="output_path" 
                                   value="<?php echo $settings['output_path'] ?? '/uploads/videos/'; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label d-block mb-2">자동 업로드</label>
                            <div class="custom-radio-wrapper custom-radio-horizontal">
                                <input type="radio" name="auto_upload" id="auto_upload_y" value="Y" 
                                    <?php echo (isset($settings['auto_upload']) && $settings['auto_upload'] === 'Y') ? 'checked' : ''; ?>>
                                <label for="auto_upload_y">자동 업로드</label>
                                
                                <input type="radio" name="auto_upload" id="auto_upload_n" value="N" 
                                    <?php echo (!isset($settings['auto_upload']) || $settings['auto_upload'] === 'N') ? 'checked' : ''; ?>>
                                <label for="auto_upload_n">수동 업로드</label>
                            </div>
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
$('#videocreateForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: '/admin/plugin/videocreate',
        method: 'POST',
        data: {
            enabled: $('input[name="enabled"]:checked').val(),
            api_key: $('#api_key').val(),
            video_quality: $('#video_quality').val(),
            output_path: $('#output_path').val(),
            auto_upload: $('input[name="auto_upload"]:checked').val()
        },
        success: function(response) {
            if (response.success) alert(response.message);
        }
    });
});
</script>

<?php include __DIR__ . '/../../_footer.php'; ?>
