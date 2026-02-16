<?php include __DIR__ . '/_admin_header.php'; ?>

<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4" style="background-color: var(--main-bg);">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-robot me-2"></i><?= xssFilter($title) ?>
            </h2>
        </div>

        <style>
            .bot-card-custom {
                border: 1px solid #dee2e6;
                border-radius: 12px;
                padding: 12px 16px;
                background-color: #fff;
                transition: all 0.2s ease;
                cursor: pointer;
                margin-bottom: 8px;
            }
            
            .bot-card-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.08);
                border-color: #adb5bd;
            }
            
            .bot-card-custom label {
                display: flex;
                align-items: center;
                cursor: pointer;
                margin: 0;
                width: 100%;
            }
            
            .bot-card-custom input[type="checkbox"] {
                width: 20px;
                height: 20px;
                margin-right: 12px;
                cursor: pointer;
                flex-shrink: 0;
                accent-color: #28a745;
            }
            
            .bot-info {
                flex: 1;
            }
            
            .bot-name {
                font-weight: 600;
                color: #212529;
                font-size: 0.95rem;
            }
            
            .bot-description {
                font-size: 0.8rem;
                color: #6c757d;
                margin-top: 2px;
            }
            
            .bot-user-agent {
                font-size: 0.7rem;
                color: #adb5bd;
                font-family: 'Courier New', monospace;
                margin-top: 2px;
            }
            
            .bot-card-custom input[type="checkbox"]:checked ~ .bot-info .bot-name {
                color: #28a745;
                font-weight: 700;
            }
            
            .bot-card-custom.selected {
                background-color: #f0f9f4;
                border-color: #28a745;
            }
            
            .select-buttons {
                display: flex;
                gap: 8px;
                margin-bottom: 15px;
            }
            
            .select-buttons .btn {
                font-size: 0.875rem;
                padding: 6px 12px;
            }
            
            .bot-checkbox-container {
                max-height: 600px;
                overflow-y: auto;
                padding-right: 10px;
            }
            
            .bot-checkbox-container::-webkit-scrollbar {
                width: 6px;
            }
            
            .bot-checkbox-container::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 10px;
            }
            
            .bot-checkbox-container::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 10px;
            }
            
            .bot-checkbox-container::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        </style>

        <form id="botForm">
            <!-- 2열 레이아웃 -->
            <div class="row">
                <!-- 왼쪽: 검색 엔진 봇 -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-search me-2"></i>주요 검색엔진/서비스 크롤러 봇 (<?= count($searchBots) ?>개)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="select-buttons">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllSearch(true)">
                                    <i class="bi bi-check-square"></i> 전체선택
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllSearch(false)">
                                    <i class="bi bi-square"></i> 해제
                                </button>
                            </div>
                            
                            <div class="bot-checkbox-container">
                                <?php foreach ($searchBots as $bot): ?>
                                <?php 
                                $checked = in_array($bot['user_agent'], $allowedBots) ? 'checked' : '';
                                $selectedClass = $checked ? 'selected' : '';
                                ?>
                                <div class="bot-card-custom search-bot-card <?= $selectedClass ?>">
                                    <label>
                                        <input type="checkbox" 
                                               class="search-bot-checkbox"
                                               name="allowed_bots[]" 
                                               value="<?= xssFilter($bot['user_agent']) ?>"
                                               <?= $checked ?>
                                               onchange="updateCardStyle(this)">
                                        <div class="bot-info">
                                            <div class="bot-name"><?= xssFilter($bot['bot_name']) ?></div>
                                            <div class="bot-description"><?= xssFilter($bot['description']) ?></div>
                                            <div class="bot-user-agent">User-agent: <?= xssFilter($bot['user_agent']) ?></div>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 오른쪽: AI 봇 -->
                <div class="col-md-6 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-cpu me-2"></i>AI/LLM 데이터 수집 봇 (<?= count($aiBots) ?>개)
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="select-buttons">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="toggleAllAI(true)">
                                    <i class="bi bi-check-square"></i> 전체선택
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllAI(false)">
                                    <i class="bi bi-square"></i> 해제
                                </button>
                            </div>
                            
                            <div class="bot-checkbox-container">
                                <?php foreach ($aiBots as $bot): ?>
                                <?php 
                                $checked = in_array($bot['user_agent'], $allowedBots) ? 'checked' : '';
                                $selectedClass = $checked ? 'selected' : '';
                                ?>
                                <div class="bot-card-custom ai-bot-card <?= $selectedClass ?>">
                                    <label>
                                        <input type="checkbox" 
                                               class="ai-bot-checkbox"
                                               name="allowed_bots[]" 
                                               value="<?= xssFilter($bot['user_agent']) ?>"
                                               <?= $checked ?>
                                               onchange="updateCardStyle(this)">
                                        <div class="bot-info">
                                            <div class="bot-name"><?= xssFilter($bot['bot_name']) ?></div>
                                            <div class="bot-description"><?= xssFilter($bot['description']) ?></div>
                                            <div class="bot-user-agent">User-agent: <?= xssFilter($bot['user_agent']) ?></div>
                                        </div>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 저장 버튼 -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="button" class="btn btn-success btn-lg w-100" onclick="saveBotSettings()">
                        <i class="bi bi-save me-2"></i>BOT 설정 저장 및 robots.txt 업데이트
                    </button>
                </div>
            </div>
        </form>

        <!-- robots.txt 미리보기 -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning">
                <h5 class="mb-0">
                    <i class="bi bi-file-text me-2"></i>robots.txt 미리보기
                </h5>
            </div>
            <div class="card-body">
                <textarea class="form-control" rows="20" readonly style="font-family: 'Courier New', monospace; font-size: 0.875rem;"><?= xssFilter($robotsTxt) ?></textarea>
                <div class="alert alert-info mt-3 mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>체크된 봇만 Allow로 허용</strong>되며, 나머지는 <strong>Disallow로 차단</strong>됩니다.
                    <br>모든 봇은 <code>/admin/</code>, <code>/member/</code>, <code>/api/</code> 경로 접근이 차단됩니다.
                </div>
            </div>
        </div>

    </main>
</div>

<script>
// 카드 스타일 업데이트
function updateCardStyle(checkbox) {
    const card = checkbox.closest('.bot-card-custom');
    if (checkbox.checked) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
}

// 검색엔진 봇 전체 선택/해제
function toggleAllSearch(checked) {
    const checkboxes = document.querySelectorAll('.search-bot-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
        updateCardStyle(checkbox);
    });
}

// AI 봇 전체 선택/해제
function toggleAllAI(checked) {
    const checkboxes = document.querySelectorAll('.ai-bot-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
        updateCardStyle(checkbox);
    });
}

// BOT 설정 저장
function saveBotSettings() {
    const form = document.getElementById('botForm');
    const formData = new FormData(form);
    
    fetch('/admin/bot/save', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('저장 중 오류가 발생했습니다.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('저장 중 오류가 발생했습니다.');
    });
}
</script>

</body>
</html>
