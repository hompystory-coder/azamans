<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-search text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="row">
            <!-- 기본 메타 태그 -->
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>기본 메타 태그</h5>
                    </div>
                    <div class="card-body">
                        <form id="seoForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">메타 타이틀 (Title)</label>
                                <input type="text" class="form-control" name="meta_title" 
                                       value="<?php echo xssFilter($meta_title); ?>" 
                                       placeholder="사이트 제목">
                                <small class="text-muted">검색 결과에 표시되는 제목 (50-60자 권장)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">메타 설명 (Description)</label>
                                <textarea class="form-control" name="meta_description" rows="3" 
                                          placeholder="사이트 설명"><?php echo xssFilter($meta_description); ?></textarea>
                                <small class="text-muted">검색 결과에 표시되는 설명 (150-160자 권장)</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">메타 키워드 (Keywords)</label>
                                <input type="text" class="form-control" name="meta_keywords" 
                                       value="<?php echo xssFilter($meta_keywords); ?>" 
                                       placeholder="키워드1, 키워드2, 키워드3">
                                <small class="text-muted">콤마(,)로 구분하여 입력</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Open Graph (OG) 태그 -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fab fa-facebook me-2"></i>오픈 그래프 (Open Graph)</h5>
                    </div>
                    <div class="card-body">
                        <form id="ogForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">OG 타이틀</label>
                                <input type="text" class="form-control" name="og_title" 
                                       value="<?php echo xssFilter($og_title); ?>" 
                                       placeholder="SNS 공유 시 표시될 제목">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">OG 설명</label>
                                <textarea class="form-control" name="og_description" rows="3" 
                                          placeholder="SNS 공유 시 표시될 설명"><?php echo xssFilter($og_description); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">OG 이미지 URL</label>
                                <input type="url" class="form-control" name="og_image" 
                                       value="<?php echo xssFilter($og_image); ?>" 
                                       placeholder="https://example.com/image.jpg">
                                <small class="text-muted">1200x630 픽셀 권장</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Twitter Card -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fab fa-twitter me-2"></i>트위터 카드</h5>
                    </div>
                    <div class="card-body">
                        <form id="twitterForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">트위터 카드 타입</label>
                                <select class="form-select" name="twitter_card">
                                    <option value="summary" <?php echo $twitter_card === 'summary' ? 'selected' : ''; ?>>Summary</option>
                                    <option value="summary_large_image" <?php echo $twitter_card === 'summary_large_image' ? 'selected' : ''; ?>>Summary Large Image</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-info">
                                <h6 class="alert-heading">💡 참고사항</h6>
                                <ul class="mb-0 small">
                                    <li><strong>Summary:</strong> 작은 이미지와 텍스트</li>
                                    <li><strong>Summary Large Image:</strong> 큰 이미지 강조</li>
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- SEO 체크리스트 -->
            <div class="col-12 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>SEO 체크리스트</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">📋 SEO 최적화 체크리스트</h6>
                            <ul class="mb-0">
                                <li>✅ 메타 타이틀: 50-60자 이내</li>
                                <li>✅ 메타 설명: 150-160자 이내</li>
                                <li>✅ OG 이미지: 1200x630 픽셀 권장</li>
                                <li>✅ Sitemap: <a href="/sitemap_index.xml" target="_blank">/sitemap_index.xml</a> 생성 확인</li>
                                <li>✅ robots.txt: <a href="/robots.txt" target="_blank">/robots.txt</a> 설정 확인</li>
                                <li>✅ 검색엔진 등록: 네이버 웹마스터, 구글 서치 콘솔</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 저장 버튼 -->
            <div class="col-12">
                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary btn-lg" onclick="saveSeoSettings()">
                        <i class="fas fa-save me-2"></i>설정 저장
                    </button>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
function saveSeoSettings() {
    const formData = new FormData();
    
    // 기본 메타 태그
    formData.append('meta_title', document.querySelector('input[name="meta_title"]').value);
    formData.append('meta_description', document.querySelector('textarea[name="meta_description"]').value);
    formData.append('meta_keywords', document.querySelector('input[name="meta_keywords"]').value);
    
    // Open Graph
    formData.append('og_title', document.querySelector('input[name="og_title"]').value);
    formData.append('og_description', document.querySelector('textarea[name="og_description"]').value);
    formData.append('og_image', document.querySelector('input[name="og_image"]').value);
    
    // Twitter Card
    formData.append('twitter_card', document.querySelector('select[name="twitter_card"]').value);
    
    fetch('/admin/seo/save', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) { 
            alert('SEO 설정이 저장되었습니다.'); 
            location.reload(); 
        } else { 
            alert('오류: ' + data.message); 
        }
    })
    .catch(err => {
        alert('저장 중 오류가 발생했습니다.');
        console.error(err);
    });
}
</script>
</body></html>
