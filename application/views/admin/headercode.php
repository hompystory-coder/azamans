<?php include __DIR__ . '/_admin_header.php'; ?>
<div class="d-flex">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1"><?= xssFilter($title) ?></h2>
                <p class="text-muted mb-0"><?= date('Y년 m월 d일 H:i') ?></p>
            </div>
        </div>
        
        <!-- 안내 -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>헤더 코드란?</strong> 모든 페이지의 &lt;head&gt; 태그 안에 삽입되는 HTML/JavaScript 코드입니다. 
            Google Analytics, Facebook Pixel 등의 추적 스크립트를 추가할 수 있습니다.
        </div>
        
        <!-- 헤더 코드 설정 -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-code"></i> 헤더 코드 설정
                </h5>
            </div>
            <div class="card-body">
                <form id="headerCodeForm">
                    <div class="mb-3">
                        <label class="form-label">HTML/JavaScript 코드</label>
                        <textarea class="form-control font-monospace" name="header_code" id="headerCode" rows="15" placeholder="여기에 헤더 코드를 입력하세요..."><?= xssFilter($header_code ?? '') ?></textarea>
                        <small class="text-muted">
                            예시: Google Analytics, Meta Pixel, 기타 추적 스크립트 등
                        </small>
                    </div>
                    
                    <button type="button" class="btn btn-primary" onclick="saveCode()">
                        <i class="fas fa-save me-2"></i>저장
                    </button>
                </form>
            </div>
        </div>
        
        <!-- 예시 코드 -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb"></i> 예시 코드
                </h5>
            </div>
            <div class="card-body">
                <h6>Google Analytics 4</h6>
                <pre class="bg-light p-3 rounded"><code>&lt;!-- Google tag (gtag.js) --&gt;
&lt;script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"&gt;&lt;/script&gt;
&lt;script&gt;
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
&lt;/script&gt;</code></pre>

                <h6 class="mt-3">Meta (Facebook) Pixel</h6>
                <pre class="bg-light p-3 rounded"><code>&lt;!-- Meta Pixel Code --&gt;
&lt;script&gt;
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', 'YOUR_PIXEL_ID');
fbq('track', 'PageView');
&lt;/script&gt;</code></pre>
            </div>
        </div>
        
    </main>
</div>

<script>
function saveCode() {
    const headerCode = document.getElementById('headerCode').value;
    
    fetch('/admin/config/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'config_key=header_code&config_value=' + encodeURIComponent(headerCode)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('헤더 코드가 저장되었습니다.');
        } else {
            alert(data.message || '저장에 실패했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('네트워크 오류가 발생했습니다.');
    });
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
