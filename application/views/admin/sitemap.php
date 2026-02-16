<?php
/**
 * Sitemap 관리 페이지
 */

// Sitemap 설정 가져오기
$sitemapEnabled = getConfig('sitemap_enabled', 'Y');
$sitemapItemLimit = getConfig('sitemap_item_limit', '50000');
$sitemapBbsEnabled = getConfig('sitemap_bbs_enabled', 'Y');
$sitemapNewsEnabled = getConfig('sitemap_news_enabled', 'Y');
$sitemapBbsList = getConfig('sitemap_bbs_list', '');
$sitemapNewsList = getConfig('sitemap_news_list', '');

// 게시판 목록 가져오기
$bbsList = getDbArray("SELECT bbs_id, bbs_name FROM bbs_list ORDER BY bbs_id");
$selectedBbs = $sitemapBbsList ? explode(',', $sitemapBbsList) : [];

// 뉴스 목록 가져오기
$newsList = getDbArray("SELECT news_id, news_name FROM news_list ORDER BY news_id");
$selectedNews = $sitemapNewsList ? explode(',', $sitemapNewsList) : [];
?>

<?php include APP_PATH . '/views/admin/_admin_header.php'; ?>

<style>
/* 2열 레이아웃 컨테이너 */
.two-column-container {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}

.column-card {
    flex: 1;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.column-card-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
}

.column-card-body {
    padding: 20px;
}

/* 스크롤 컨테이너 */
.scroll-container {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 10px;
}

.scroll-container::-webkit-scrollbar {
    width: 6px;
}

.scroll-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.scroll-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* 체크박스 항목 카드 */
.checkbox-item-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
}

.checkbox-item-card:hover {
    border-color: #adb5bd;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.checkbox-item-card.selected {
    background: linear-gradient(135deg, #28a74515 0%, #20c99715 100%);
    border-color: #28a745;
}

/* 체크박스 스타일 */
.checkbox-item-card input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    flex-shrink: 0;
}

.checkbox-item-card label {
    cursor: pointer;
    margin: 0;
    flex-grow: 1;
    font-weight: 500;
    color: #495057;
}

.checkbox-item-card.selected label {
    color: #28a745;
    font-weight: 600;
}

/* Switch 스타일 */
.custom-switch-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
}

.custom-switch-wrapper input[type="checkbox"] {
    position: relative;
    width: 48px;
    height: 24px;
    appearance: none;
    background: #ccc;
    border-radius: 24px;
    outline: none;
    cursor: pointer;
    transition: background 0.3s;
}

.custom-switch-wrapper input[type="checkbox"]:checked {
    background: #28a745;
}

.custom-switch-wrapper input[type="checkbox"]::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}

.custom-switch-wrapper input[type="checkbox"]:checked::before {
    transform: translateX(24px);
}

.custom-switch-wrapper label {
    cursor: pointer;
    user-select: none;
    margin: 0;
    font-weight: 500;
}

/* 버튼 스타일 */
.select-buttons {
    display: flex;
    gap: 8px;
    margin-bottom: 15px;
}

.select-buttons button {
    font-size: 0.875rem;
    padding: 6px 12px;
}
</style>

<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4" style="background-color: #f8f9fa;">
        <!-- 페이지 제목 -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0"><i class="fas fa-sitemap"></i> Sitemap 설정</h4>
                <small class="text-muted">오늘 날짜: <?php echo date('Y-m-d'); ?></small>
            </div>
        </div>

        <!-- 메인 카드 -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                
                <!-- 기본 설정 -->
                <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-cog"></i> 기본 설정
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sitemap 사용</label>
                            <select id="sitemapEnabled" class="form-select">
                                <option value="Y" <?php echo $sitemapEnabled === 'Y' ? 'selected' : ''; ?>>사용함</option>
                                <option value="N" <?php echo $sitemapEnabled === 'N' ? 'selected' : ''; ?>>사용 안 함</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sitemap 항목 개수</label>
                            <input type="number" id="sitemapItemLimit" class="form-control" 
                                   value="<?php echo htmlspecialchars($sitemapItemLimit); ?>" 
                                   min="100" max="50000" />
                            <small class="form-text text-muted">최대 50,000개</small>
                        </div>
                    </div>
                </div>
                
                <!-- 2열 레이아웃: 게시판 Sitemap & 뉴스 Sitemap -->
                <div class="two-column-container">
                    <!-- 왼쪽: 게시판 Sitemap -->
                    <div class="column-card">
                        <div class="column-card-header">
                            <i class="fas fa-comments"></i>
                            <span>게시판 Sitemap (<?php echo count($bbsList); ?>개)</span>
                        </div>
                        <div class="column-card-body">
                            <div class="mb-3">
                                <div class="custom-switch-wrapper">
                                    <input type="checkbox" id="sitemapBbsEnabled" 
                                           <?php echo $sitemapBbsEnabled === 'Y' ? 'checked' : ''; ?>>
                                    <label for="sitemapBbsEnabled">게시판 Sitemap 사용</label>
                                </div>
                            </div>
                            
                            <?php if (empty($bbsList)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    등록된 게시판이 없습니다.
                                </div>
                            <?php else: ?>
                                <div class="select-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAllBbs()">
                                        <i class="fas fa-check-square"></i> 전체 선택
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllBbs()">
                                        <i class="fas fa-square"></i> 전체 해제
                                    </button>
                                </div>
                                
                                <div class="scroll-container">
                                    <?php foreach ($bbsList as $bbs): ?>
                                        <div class="checkbox-item-card <?php echo in_array($bbs['bbs_id'], $selectedBbs) ? 'selected' : ''; ?>" 
                                             onclick="toggleCheckbox('bbs_<?php echo htmlspecialchars($bbs['bbs_id']); ?>')">
                                            <input type="checkbox" 
                                                   class="bbs-checkbox" 
                                                   id="bbs_<?php echo htmlspecialchars($bbs['bbs_id']); ?>" 
                                                   value="<?php echo htmlspecialchars($bbs['bbs_id']); ?>"
                                                   <?php echo in_array($bbs['bbs_id'], $selectedBbs) ? 'checked' : ''; ?>
                                                   onclick="event.stopPropagation();">
                                            <label for="bbs_<?php echo htmlspecialchars($bbs['bbs_id']); ?>"
                                                   onclick="event.stopPropagation();">
                                                <?php echo htmlspecialchars($bbs['bbs_name']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- 오른쪽: 뉴스 Sitemap -->
                    <div class="column-card">
                        <div class="column-card-header">
                            <i class="fas fa-newspaper"></i>
                            <span>뉴스 Sitemap (<?php echo count($newsList); ?>개)</span>
                        </div>
                        <div class="column-card-body">
                            <div class="mb-3">
                                <div class="custom-switch-wrapper">
                                    <input type="checkbox" id="sitemapNewsEnabled" 
                                           <?php echo $sitemapNewsEnabled === 'Y' ? 'checked' : ''; ?>>
                                    <label for="sitemapNewsEnabled">뉴스 Sitemap 사용</label>
                                </div>
                            </div>
                            
                            <?php if (empty($newsList)): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    등록된 뉴스가 없습니다.
                                </div>
                            <?php else: ?>
                                <div class="select-buttons">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAllNews()">
                                        <i class="fas fa-check-square"></i> 전체 선택
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllNews()">
                                        <i class="fas fa-square"></i> 전체 해제
                                    </button>
                                </div>
                                
                                <div class="scroll-container">
                                    <?php foreach ($newsList as $news): ?>
                                        <div class="checkbox-item-card <?php echo in_array($news['news_id'], $selectedNews) ? 'selected' : ''; ?>"
                                             onclick="toggleCheckbox('news_<?php echo htmlspecialchars($news['news_id']); ?>')">
                                            <input type="checkbox" 
                                                   class="news-checkbox" 
                                                   id="news_<?php echo htmlspecialchars($news['news_id']); ?>" 
                                                   value="<?php echo htmlspecialchars($news['news_id']); ?>"
                                                   <?php echo in_array($news['news_id'], $selectedNews) ? 'checked' : ''; ?>
                                                   onclick="event.stopPropagation();">
                                            <label for="news_<?php echo htmlspecialchars($news['news_id']); ?>"
                                                   onclick="event.stopPropagation();">
                                                <?php echo htmlspecialchars($news['news_name']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sitemap URL -->
                <div class="mt-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-link"></i> Sitemap URL
                    </h6>
                    
                    <div class="alert alert-info">
                        <div class="mb-2">
                            <strong>Sitemap Index:</strong> 
                            <a href="<?php echo ROOTURL; ?>/sitemap_index.xml" target="_blank">
                                <?php echo ROOTURL; ?>/sitemap_index.xml
                            </a>
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('<?php echo ROOTURL; ?>/sitemap_index.xml')">
                                <i class="fas fa-copy"></i> 복사
                            </button>
                        </div>
                        
                        <div class="mb-2">
                            <strong>게시판 Sitemap:</strong> 
                            <a href="<?php echo ROOTURL; ?>/sitemap_bbs.xml" target="_blank">
                                <?php echo ROOTURL; ?>/sitemap_bbs.xml
                            </a>
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('<?php echo ROOTURL; ?>/sitemap_bbs.xml')">
                                <i class="fas fa-copy"></i> 복사
                            </button>
                        </div>
                        
                        <div>
                            <strong>뉴스 Sitemap:</strong> 
                            <a href="<?php echo ROOTURL; ?>/sitemap_news.xml" target="_blank">
                                <?php echo ROOTURL; ?>/sitemap_news.xml
                            </a>
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyToClipboard('<?php echo ROOTURL; ?>/sitemap_news.xml')">
                                <i class="fas fa-copy"></i> 복사
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 검색엔진 등록 바로가기 -->
                <div class="mt-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fas fa-search"></i> 검색엔진 등록
                    </h6>
                    
                    <div class="alert alert-light border">
                        <div class="d-flex flex-wrap gap-3">
                            <a href="https://search.google.com/search-console" target="_blank" class="btn btn-outline-danger">
                                <i class="fab fa-google"></i> Google Search Console
                            </a>
                            <a href="https://searchadvisor.naver.com/" target="_blank" class="btn btn-outline-success">
                                <i class="fas fa-n"></i> 네이버 서치어드바이저
                            </a>
                            <a href="https://www.bing.com/webmasters" target="_blank" class="btn btn-outline-info">
                                <i class="fab fa-microsoft"></i> Bing 웹마스터 도구
                            </a>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> 
                            위 링크에서 Sitemap을 등록하여 검색엔진에 사이트를 노출시킬 수 있습니다.
                        </small>
                        
                        <!-- 등록안내 버튼 -->
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleGuide()">
                                <i class="fas fa-book-open"></i> 등록안내 보기
                            </button>
                        </div>
                        
                        <!-- 등록안내 내용 (접혀있음) -->
                        <div id="registrationGuide" style="display: none;" class="mt-3">
                            <div class="accordion" id="searchEngineGuide">
                                <!-- Google Search Console -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingGoogle">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGoogle">
                                            <i class="fab fa-google text-danger me-2"></i>
                                            <strong>Google Search Console 등록방법</strong>
                                        </button>
                                    </h2>
                                    <div id="collapseGoogle" class="accordion-collapse collapse" data-bs-parent="#searchEngineGuide">
                                        <div class="accordion-body">
                                            <ol class="mb-3">
                                                <li><a href="https://search.google.com/search-console" target="_blank">Google Search Console</a>에 접속하여 로그인</li>
                                                <li>좌측 메뉴에서 <strong>"속성 추가"</strong> 클릭</li>
                                                <li><strong>"URL 접두어"</strong> 선택 후 사이트 URL 입력</li>
                                                <li>소유권 확인 (HTML 파일 업로드 또는 메타 태그 추가)</li>
                                                <li>좌측 메뉴 <strong>"Sitemaps"</strong> 선택</li>
                                                <li>Sitemap URL 입력: <code><?php echo ROOTURL; ?>/sitemap_index.xml</code></li>
                                                <li><strong>"제출"</strong> 버튼 클릭</li>
                                            </ol>
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                <strong>자세한 가이드:</strong>
                                                <a href="https://support.google.com/webmasters/answer/9008080" target="_blank" class="ms-2">
                                                    Google 공식 문서 보기
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 네이버 서치어드바이저 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingNaver">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNaver">
                                            <i class="fas fa-n text-success me-2"></i>
                                            <strong>네이버 서치어드바이저 등록방법</strong>
                                        </button>
                                    </h2>
                                    <div id="collapseNaver" class="accordion-collapse collapse" data-bs-parent="#searchEngineGuide">
                                        <div class="accordion-body">
                                            <ol class="mb-3">
                                                <li><a href="https://searchadvisor.naver.com/" target="_blank">네이버 서치어드바이저</a>에 접속하여 로그인</li>
                                                <li>상단 <strong>"웹마스터 도구"</strong> 클릭</li>
                                                <li><strong>"사이트 등록"</strong> 버튼 클릭 후 사이트 URL 입력</li>
                                                <li>소유권 확인 (HTML 파일 업로드 또는 메타 태그 추가)</li>
                                                <li>좌측 메뉴 <strong>"요청 > 사이트맵 제출"</strong> 선택</li>
                                                <li>Sitemap URL 입력: <code><?php echo ROOTURL; ?>/sitemap_index.xml</code></li>
                                                <li><strong>"확인"</strong> 버튼 클릭</li>
                                            </ol>
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                <strong>자세한 가이드:</strong>
                                                <a href="https://searchadvisor.naver.com/guide" target="_blank" class="ms-2">
                                                    네이버 서치어드바이저 가이드
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Bing 웹마스터 도구 -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingBing">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBing">
                                            <i class="fab fa-microsoft text-info me-2"></i>
                                            <strong>Bing 웹마스터 도구 등록방법</strong>
                                        </button>
                                    </h2>
                                    <div id="collapseBing" class="accordion-collapse collapse" data-bs-parent="#searchEngineGuide">
                                        <div class="accordion-body">
                                            <ol class="mb-3">
                                                <li><a href="https://www.bing.com/webmasters" target="_blank">Bing 웹마스터 도구</a>에 접속하여 로그인</li>
                                                <li><strong>"사이트 추가"</strong> 버튼 클릭</li>
                                                <li>사이트 URL, Sitemap URL 입력</li>
                                                <li>Sitemap URL: <code><?php echo ROOTURL; ?>/sitemap_index.xml</code></li>
                                                <li>소유권 확인 (XML 파일 업로드 또는 메타 태그 추가)</li>
                                                <li>좌측 메뉴 <strong>"Sitemaps"</strong>에서 제출 상태 확인</li>
                                            </ol>
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-lightbulb me-1"></i>
                                                <strong>팁:</strong>
                                                Google Search Console 계정이 있다면 <strong>"Google에서 가져오기"</strong> 옵션으로 간편하게 등록할 수 있습니다.
                                            </div>
                                            <div class="alert alert-info mb-0 mt-2">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                <strong>자세한 가이드:</strong>
                                                <a href="https://www.bing.com/webmasters/help/getting-started-checklist-66a806de" target="_blank" class="ms-2">
                                                    Bing 공식 문서 보기
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- 저장 버튼 -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-success" onclick="saveSitemapSettings()">
                        <i class="fas fa-save"></i> 설정 저장
                    </button>
                    
                    <button type="button" class="btn btn-primary" onclick="regenerateSitemap()">
                        <i class="fas fa-sync"></i> Sitemap 재생성
                    </button>
                </div>
                
            </div>
        </div>
    </main>
</div>

<script>
// 체크박스 토글
function toggleCheckbox(id) {
    const checkbox = document.getElementById(id);
    checkbox.checked = !checkbox.checked;
    updateCardStyle(checkbox);
}

// 카드 스타일 업데이트
function updateCardStyle(checkbox) {
    const card = checkbox.closest('.checkbox-item-card');
    if (checkbox.checked) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
}

// 모든 체크박스에 이벤트 리스너 추가
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.checkbox-item-card input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCardStyle(this);
        });
    });
});

// 게시판 전체 선택
function selectAllBbs() {
    document.querySelectorAll('.bbs-checkbox').forEach(cb => {
        cb.checked = true;
        updateCardStyle(cb);
    });
}

// 게시판 전체 해제
function deselectAllBbs() {
    document.querySelectorAll('.bbs-checkbox').forEach(cb => {
        cb.checked = false;
        updateCardStyle(cb);
    });
}

// 뉴스 전체 선택
function selectAllNews() {
    document.querySelectorAll('.news-checkbox').forEach(cb => {
        cb.checked = true;
        updateCardStyle(cb);
    });
}

// 뉴스 전체 해제
function deselectAllNews() {
    document.querySelectorAll('.news-checkbox').forEach(cb => {
        cb.checked = false;
        updateCardStyle(cb);
    });
}

// 등록안내 토글
function toggleGuide() {
    const guide = document.getElementById('registrationGuide');
    const button = event.target.closest('button');
    
    if (guide.style.display === 'none') {
        guide.style.display = 'block';
        button.innerHTML = '<i class="fas fa-book-open"></i> 등록안내 닫기';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-secondary');
    } else {
        guide.style.display = 'none';
        button.innerHTML = '<i class="fas fa-book-open"></i> 등록안내 보기';
        button.classList.remove('btn-secondary');
        button.classList.add('btn-outline-secondary');
    }
}

// 클립보드 복사
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        $('#ai-pop').pgpopup({
            type: 'toast',
            msg: 'URL이 복사되었습니다.',
            padding: '15px',
            width: '250',
            color: '#ffffff',
            bgcolor: '#28a745',
            transparency: '0.9',
            delay: '1500',
            time: '500'
        });
    }).catch(err => {
        console.error('복사 실패:', err);
        $('#ai-pop').pgpopup({
            type: 'toast',
            msg: '복사에 실패했습니다.',
            padding: '15px',
            width: '250',
            color: '#ffffff',
            bgcolor: '#dc3545',
            transparency: '0.9',
            delay: '1500',
            time: '500'
        });
    });
}

// Sitemap 설정 저장
function saveSitemapSettings() {
    const bbsCheckboxes = document.querySelectorAll('.bbs-checkbox:checked');
    const newsCheckboxes = document.querySelectorAll('.news-checkbox:checked');
    
    const bbsList = Array.from(bbsCheckboxes).map(cb => cb.value).join(',');
    const newsList = Array.from(newsCheckboxes).map(cb => cb.value).join(',');
    
    const data = {
        sitemap_enabled: document.getElementById('sitemapEnabled').value,
        sitemap_item_limit: document.getElementById('sitemapItemLimit').value,
        sitemap_bbs_enabled: document.getElementById('sitemapBbsEnabled').checked ? 'Y' : 'N',
        sitemap_news_enabled: document.getElementById('sitemapNewsEnabled').checked ? 'Y' : 'N',
        sitemap_bbs_list: bbsList,
        sitemap_news_list: newsList
    };
    
    fetch('<?php echo ROOTURL; ?>/index.php?url=admin/sitemap', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'save',
            ...data
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            $('#ai-pop').pgpopup({
                type: 'toast',
                msg: 'Sitemap 설정이 저장되었습니다.',
                padding: '15px',
                width: '250',
                color: '#ffffff',
                bgcolor: '#28a745',
                transparency: '0.9',
                delay: '1500',
                time: '500'
            });
        } else {
            $('#ai-pop').pgpopup({
                type: 'toast',
                msg: '저장에 실패했습니다: ' + (result.message || ''),
                padding: '15px',
                width: '300',
                color: '#ffffff',
                bgcolor: '#dc3545',
                transparency: '0.9',
                delay: '2000',
                time: '500'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $('#ai-pop').pgpopup({
            type: 'toast',
            msg: '저장 중 오류가 발생했습니다.',
            padding: '15px',
            width: '250',
            color: '#ffffff',
            bgcolor: '#dc3545',
            transparency: '0.9',
            delay: '1500',
            time: '500'
        });
    });
}

// Sitemap 재생성
function regenerateSitemap() {
    if (!confirm('Sitemap을 재생성하시겠습니까?')) {
        return;
    }
    
    fetch('<?php echo ROOTURL; ?>/index.php?url=admin/sitemap', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'regenerate'
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            $('#ai-pop').pgpopup({
                type: 'toast',
                msg: 'Sitemap이 재생성되었습니다.',
                padding: '15px',
                width: '250',
                color: '#ffffff',
                bgcolor: '#0d6efd',
                transparency: '0.9',
                delay: '1500',
                time: '500'
            });
        } else {
            $('#ai-pop').pgpopup({
                type: 'toast',
                msg: '재생성에 실패했습니다: ' + (result.message || ''),
                padding: '15px',
                width: '300',
                color: '#ffffff',
                bgcolor: '#dc3545',
                transparency: '0.9',
                delay: '2000',
                time: '500'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        $('#ai-pop').pgpopup({
            type: 'toast',
            msg: '재생성 중 오류가 발생했습니다.',
            padding: '15px',
            width: '250',
            color: '#ffffff',
            bgcolor: '#dc3545',
            transparency: '0.9',
            delay: '1500',
            time: '500'
        });
    });
}
</script>
