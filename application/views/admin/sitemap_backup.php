<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold mb-0">
                <i class="fas fa-rss text-main me-2"></i><?php echo xssFilter($title); ?>
            </h1>
        </div>
        
        <div class="row">
            <!-- RSS 설정 -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-rss me-2"></i>RSS 설정</h5>
                    </div>
                    <div class="card-body">
                        <form id="rssForm">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">RSS 추출 게시판</label>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="toggleAllRss(true)">
                                            <i class="fas fa-check-square me-1"></i>전체선택
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllRss(false)">
                                            <i class="fas fa-square me-1"></i>해제
                                        </button>
                                    </div>
                                </div>
                                <div class="custom-checkbox-wrapper">
                                <?php foreach ($boards as $board): ?>
                                    <label>
                                        <input type="checkbox" id="rss_board_<?php echo $board['bbs_id']; ?>" 
                                               name="rss_boards[]" 
                                               class="rss-checkbox"
                                               value="<?php echo $board['bbs_id']; ?>"
                                               <?php echo in_array($board['bbs_id'], $rss_boards) ? 'checked' : ''; ?>>
                                        <span><?php echo xssFilter($board['bbs_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">추출 기간 (일)</label>
                                <input type="number" class="form-control" name="rss_period" value="<?php echo $rss_period; ?>" min="1" max="365">
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-primary" onclick="saveRss()">
                                    <i class="fas fa-save me-1"></i>저장
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sitemap 설정 - 게시판 -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Sitemap 게시판 설정</h5>
                    </div>
                    <div class="card-body">
                        <form id="sitemapBbsForm">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <label class="form-label fw-bold mb-0">Sitemap에서 제외할 게시판</label>
                                        <small class="text-muted d-block">선택한 게시판은 sitemap에서 제외됩니다</small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-success me-1" onclick="toggleAllSitemapBbs(true)">
                                            <i class="fas fa-check-square me-1"></i>전체선택
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllSitemapBbs(false)">
                                            <i class="fas fa-square me-1"></i>해제
                                        </button>
                                    </div>
                                </div>
                                <div class="custom-checkbox-wrapper">
                                <?php foreach ($boards as $board): ?>
                                    <label>
                                        <input type="checkbox" id="sitemap_bbs_<?php echo $board['bbs_id']; ?>" 
                                               name="sitemap_exclude_bbs[]" 
                                               class="sitemap-bbs-checkbox"
                                               value="<?php echo $board['bbs_id']; ?>"
                                               <?php echo in_array($board['bbs_id'], $sitemap_exclude_bbs) ? 'checked' : ''; ?>>
                                        <span><?php echo xssFilter($board['bbs_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-success" onclick="saveSitemapBbs()">
                                    <i class="fas fa-save me-1"></i>저장
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sitemap 설정 - 뉴스 -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Sitemap 뉴스 설정</h5>
                    </div>
                    <div class="card-body">
                        <form id="sitemapNewsForm">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <label class="form-label fw-bold mb-0">Sitemap에서 제외할 뉴스</label>
                                        <small class="text-muted d-block">선택한 뉴스는 sitemap에서 제외됩니다</small>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="toggleAllSitemapNews(true)">
                                            <i class="fas fa-check-square me-1"></i>전체선택
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllSitemapNews(false)">
                                            <i class="fas fa-square me-1"></i>해제
                                        </button>
                                    </div>
                                </div>
                                <div class="custom-checkbox-wrapper">
                                <?php foreach ($newsList as $news): ?>
                                    <label>
                                        <input type="checkbox" id="sitemap_news_<?php echo $news['news_id']; ?>" 
                                               name="sitemap_exclude_news[]" 
                                               class="sitemap-news-checkbox"
                                               value="<?php echo $news['news_id']; ?>"
                                               <?php echo in_array($news['news_id'], $sitemap_exclude_news) ? 'checked' : ''; ?>>
                                        <span><?php echo xssFilter($news['news_name']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-info" onclick="saveSitemapNews()">
                                    <i class="fas fa-save me-1"></i>저장
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sitemap 파일 정보 -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-file-code me-2"></i>Sitemap 파일 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">Sitemap 구조 (동적 생성)</h6>
                            <ul class="mb-0 small">
                                <li><strong>Index:</strong> <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sitemap_index.xml" target="_blank"><?php echo $_SERVER['HTTP_HOST']; ?>/sitemap_index.xml</a></li>
                                <li><strong>게시판:</strong> <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sitemap_bbs.xml" target="_blank"><?php echo $_SERVER['HTTP_HOST']; ?>/sitemap_bbs.xml</a></li>
                                <li><strong>뉴스:</strong> <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/sitemap_news.xml" target="_blank"><?php echo $_SERVER['HTTP_HOST']; ?>/sitemap_news.xml</a></li>
                            </ul>
                            <hr>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Sitemap은 동적으로 생성되며 1시간 캐싱됩니다. 게시물 작성/수정/삭제 시 자동으로 캐시가 갱신됩니다.
                            </small>
                        </div>
                        
                        <button type="button" class="btn btn-warning w-100 mt-3" onclick="generateAllSitemaps()">
                            <i class="fas fa-sync-alt me-1"></i>Sitemap 캐시 초기화
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<style>
/* 체크박스 리스트 세로 정렬 */
.custom-checkbox-wrapper {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* 카드형 체크박스 */
.custom-checkbox-wrapper label {
    display: flex;
    align-items: center;
    padding: 15px 18px;
    border-radius: 14px;
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.06);
    cursor: pointer;
    transition: all 0.22s ease;
    user-select: none;
}

.custom-checkbox-wrapper label:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

/* 기본 체크박스 숨김 */
.custom-checkbox-wrapper input[type="checkbox"] {
    display: none;
}

/* 커스텀 체크박스 */
.custom-checkbox-wrapper input[type="checkbox"] + span::before {
    content: "";
    width: 22px;
    height: 22px;
    border-radius: 7px;
    border: 2px solid #cfd3dc;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-right: 14px;
    transition: all 0.25s ease;
    flex-shrink: 0;
    vertical-align: middle;
}

/* 체크 표시 - 가상 요소로 생성 */
.custom-checkbox-wrapper input[type="checkbox"]:checked + span::before {
    background: #2563eb;
    border-color: #2563eb;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
    background-size: 14px;
    background-position: center;
    background-repeat: no-repeat;
}

/* 체크된 라벨 스타일 */
.custom-checkbox-wrapper input[type="checkbox"]:checked + span {
    font-weight: 700;
    color: #2563eb;
}

.custom-checkbox-wrapper label:has(input:checked) {
    border-color: rgba(37, 99, 235, 0.45);
    background: rgba(37, 99, 235, 0.06);
}

/* 라벨 텍스트 스타일 */
.custom-checkbox-wrapper label span {
    font-size: 15px;
    font-weight: 500;
    color: #111;
    transition: 0.25s;
    display: flex;
    align-items: center;
}
</style>
<script>
// 전체선택/해제 함수
function toggleAllRss(checked) {
    const checkboxes = document.querySelectorAll('.rss-checkbox');
    checkboxes.forEach(cb => cb.checked = checked);
}

function toggleAllSitemapBbs(checked) {
    const checkboxes = document.querySelectorAll('.sitemap-bbs-checkbox');
    checkboxes.forEach(cb => cb.checked = checked);
}

function toggleAllSitemapNews(checked) {
    const checkboxes = document.querySelectorAll('.sitemap-news-checkbox');
    checkboxes.forEach(cb => cb.checked = checked);
}

function saveRss() {
    const form = document.getElementById('rssForm');
    const formData = new FormData(form);
    fetch('/admin/rss/save', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { 
                alert('RSS 설정이 저장되었습니다.'); 
                location.reload(); 
            } else { 
                alert('오류: ' + data.message); 
            }
        });
}

function saveSitemapBbs() {
    const form = document.getElementById('sitemapBbsForm');
    const formData = new FormData(form);
    
    // 체크박스가 체크되지 않은 경우 빈 배열을 전송하기 위한 처리
    const checkboxes = form.querySelectorAll('input[name="sitemap_exclude_bbs[]"]');
    if (!Array.from(checkboxes).some(cb => cb.checked)) {
        formData.append('exclude_boards[]', '');
    } else {
        // 체크된 항목만 전송
        formData.delete('sitemap_exclude_bbs[]');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                formData.append('exclude_boards[]', cb.value);
            }
        });
    }
    
    fetch('/admin/rss/sitemap-bbs', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { 
                alert('게시판 Sitemap 설정이 저장되었습니다.\n캐시가 초기화되어 다음 접속 시 새로 생성됩니다.'); 
                location.reload(); 
            } else { 
                alert('오류: ' + data.message); 
            }
        });
}

function saveSitemapNews() {
    const form = document.getElementById('sitemapNewsForm');
    const formData = new FormData(form);
    
    // 체크박스가 체크되지 않은 경우 빈 배열을 전송하기 위한 처리
    const checkboxes = form.querySelectorAll('input[name="sitemap_exclude_news[]"]');
    if (!Array.from(checkboxes).some(cb => cb.checked)) {
        formData.append('exclude_boards[]', '');
    } else {
        // 체크된 항목만 전송
        formData.delete('sitemap_exclude_news[]');
        checkboxes.forEach(cb => {
            if (cb.checked) {
                formData.append('exclude_boards[]', cb.value);
            }
        });
    }
    
    fetch('/admin/rss/sitemap-news', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { 
                alert('뉴스 Sitemap 설정이 저장되었습니다.\n캐시가 초기화되어 다음 접속 시 새로 생성됩니다.'); 
                location.reload(); 
            } else { 
                alert('오류: ' + data.message); 
            }
        });
}

function generateAllSitemaps() {
    if (!confirm('모든 Sitemap 캐시를 초기화하시겠습니까?\n다음 접속 시 자동으로 새로 생성됩니다.')) return;
    
    fetch('/admin/rss/regenerate', { method: 'POST' })
        .then(res => res.json())
        .then(data => {
            if (data.success) { 
                alert(data.message); 
            } else { 
                alert('오류: ' + data.message); 
            }
        });
}
</script>
</body></html>
