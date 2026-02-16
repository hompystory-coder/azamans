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

        <!-- 알림 메시지 -->
        <div id="alertMessage" class="alert d-none"></div>

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

// 클립보드 복사
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('URL이 클립보드에 복사되었습니다.', 'success');
    }).catch(err => {
        console.error('복사 실패:', err);
        showAlert('복사에 실패했습니다.', 'danger');
    });
}

// 알림 표시
function showAlert(message, type = 'info') {
    const alertBox = document.getElementById('alertMessage');
    alertBox.className = `alert alert-${type}`;
    alertBox.textContent = message;
    alertBox.classList.remove('d-none');
    
    setTimeout(() => {
        alertBox.classList.add('d-none');
    }, 3000);
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
            showAlert('Sitemap 설정이 저장되었습니다.', 'success');
        } else {
            showAlert('저장에 실패했습니다: ' + (result.message || ''), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('저장 중 오류가 발생했습니다.', 'danger');
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
            showAlert('Sitemap이 재생성되었습니다.', 'success');
        } else {
            showAlert('재생성에 실패했습니다: ' + (result.message || ''), 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('재생성 중 오류가 발생했습니다.', 'danger');
    });
}
</script>

<?php include APP_PATH . '/views/admin/footercode.php'; ?>
