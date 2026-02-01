<?php include __DIR__ . '/../_header.php'; ?>

<style>
    .test-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 20px;
    }
    .info-box {
        background: #e3f2fd;
        border-left: 4px solid #2196F3;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }
    .info-box h3 {
        margin-top: 0;
        color: #1976D2;
    }
    .info-box ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    .info-box li {
        margin: 5px 0;
    }
    .btn-group {
        margin: 20px 0;
    }
    .btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        margin-right: 10px;
    }
    .btn:hover {
        background: #0056b3;
    }
    .btn-secondary {
        background: #6c757d;
    }
    .btn-secondary:hover {
        background: #545b62;
    }
    .result-box {
        margin-top: 30px;
        padding: 20px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 5px;
        display: none;
    }
    .result-box.show {
        display: block;
    }
    .result-box h3 {
        margin-top: 0;
    }
    .result-box pre {
        background: white;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
        max-height: 300px;
    }
    .version-info {
        background: #d4edda;
        border-left: 4px solid #28a745;
        padding: 15px;
        margin: 20px 0;
        border-radius: 4px;
    }
    .card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>

<main>
    <div class="test-container">
        <div class="card">
            <h1 style="color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px;">
                🎨 CKEditor 5 설치 테스트
            </h1>
            
            <div class="version-info">
                <strong>✅ 설치 완료!</strong><br>
                버전: CKEditor 5 v47.x<br>
                빌드 파일: /home/mvc/public/plugins/editor/build/ckeditor.js (<?php echo round(filesize('/home/mvc/public/plugins/editor/build/ckeditor.js') / 1024 / 1024, 2); ?>MB)<br>
                로더 파일: /home/mvc/editor.php
            </div>
            
            <div class="info-box">
                <h3>📦 포함된 무료 플러그인</h3>
                <ul>
                    <li>✅ Essentials, Autoformat, Autosave</li>
                    <li>✅ Bold, Italic, Underline, Strikethrough, Code, Subscript, Superscript</li>
                    <li>✅ Heading, Paragraph, BlockQuote, CodeBlock</li>
                    <li>✅ Font (Family, Size, Color, Background Color)</li>
                    <li>✅ Alignment, Highlight</li>
                    <li>✅ Link, AutoLink, LinkImage</li>
                    <li>✅ Image (Upload, Resize, Caption, Style, Insert)</li>
                    <li>✅ List (Bulleted, Numbered, Todo, Properties)</li>
                    <li>✅ Table (Insert, Toolbar, Properties, Cell Properties, Caption, Column Resize)</li>
                    <li>✅ Media Embed, HorizontalLine, PageBreak</li>
                    <li>✅ Indent, IndentBlock, RemoveFormat</li>
                    <li>✅ Find and Replace, SelectAll, ShowBlocks</li>
                    <li>✅ SourceEditing, HtmlEmbed, GeneralHtmlSupport</li>
                    <li>✅ SpecialCharacters, Style, Mention</li>
                    <li>✅ WordCount, PasteFromOffice</li>
                    <li>✅ RestrictedEditingMode</li>
                </ul>
            </div>
            
            <form id="testForm" method="post" action="">
                <div style="margin-bottom: 20px;">
                    <label for="content" style="display: block; margin-bottom: 10px; font-weight: bold; font-size: 18px;">
                        📝 에디터 테스트
                    </label>
                    <textarea id="content" name="content" style="width: 100%; height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><h2>CKEditor 5 테스트 페이지입니다!</h2>

<p>안녕하세요! 이것은 <strong>CKEditor 5</strong>의 테스트 페이지입니다.</p>

<h3>주요 기능:</h3>

<ul>
    <li>📝 <strong>텍스트 편집</strong>: Bold, Italic, Underline 등</li>
    <li>🎨 <strong>스타일</strong>: 글꼴, 크기, 색상 변경</li>
    <li>🖼️ <strong>이미지</strong>: 업로드, 리사이즈, 정렬</li>
    <li>📊 <strong>테이블</strong>: 삽입, 편집, 병합</li>
    <li>🔗 <strong>링크</strong>: URL 삽입, 새 탭 열기</li>
    <li>📹 <strong>미디어</strong>: YouTube, Vimeo 등 임베드</li>
    <li>💾 <strong>소스 편집</strong>: HTML 직접 편집</li>
</ul>

<p>모든 기능을 자유롭게 테스트해보세요! 🚀</p>

<blockquote>
    <p>이 에디터는 모든 무료 플러그인이 포함되어 있습니다.</p>
</blockquote></textarea>
                </div>
                
                <div class="btn-group">
                    <button type="button" class="btn" onclick="getContent()">📄 내용 가져오기</button>
                    <button type="button" class="btn btn-secondary" onclick="setContent()">✏️ 샘플 내용 설정</button>
                    <button type="button" class="btn btn-secondary" onclick="clearContent()">🗑️ 내용 지우기</button>
                </div>
            </form>
            
            <div id="resultBox" class="result-box">
                <h3>📋 에디터 내용:</h3>
                <pre id="resultContent"></pre>
            </div>
            
            <div class="info-box" style="margin-top: 30px;">
                <h3>📚 사용 방법</h3>
                <p><strong>1. 다른 PHP 파일에서 사용하기:</strong></p>
                <pre style="background: white; padding: 10px; border-radius: 5px; overflow-x: auto;">&lt;?php include '/home/mvc/editor.php'; ?&gt;

&lt;textarea id="myEditor"&gt;&lt;/textarea&gt;

&lt;?php 
initCKEditor('myEditor', [
    'height' => 600,
    'imageUploadUrl' => '/upload/image'
]); 
?&gt;</pre>
                
                <p><strong>2. 게시판 글쓰기에 적용:</strong></p>
                <pre style="background: white; padding: 10px; border-radius: 5px; overflow-x: auto;">&lt;?php include '/home/mvc/editor.php'; ?&gt;

&lt;form method="post"&gt;
    &lt;textarea id="content" name="content"&gt;&lt;/textarea&gt;
    
    &lt;?php 
    initCKEditor('content', [
        'height' => 500,
        'imageUploadUrl' => '/bbs/uploadImage'
    ]); 
    ?&gt;
    
    &lt;button type="submit"&gt;저장&lt;/button&gt;
&lt;/form&gt;</pre>
            </div>
        </div>
    </div>
</main>

<?php
// CKEditor 초기화
include '/home/mvc/editor.php';
initCKEditor('content', [
    'height' => 500,
    'imageUploadUrl' => '/bbs/uploadImage'
]);
?>

<script>
function getContent() {
    const content = window.editorcontent.getData();
    document.getElementById('resultContent').textContent = content;
    document.getElementById('resultBox').classList.add('show');
    console.log('✅ 에디터 내용:', content);
}

function setContent() {
    const sampleContent = `
<h2>🎉 새로운 샘플 내용!</h2>

<p>이것은 <strong>JavaScript</strong>로 설정한 내용입니다.</p>

<ul>
    <li>항목 1</li>
    <li>항목 2</li>
    <li>항목 3</li>
</ul>

<p style="color: #e74c3c;">빨간색 텍스트</p>
<p style="color: #3498db;">파란색 텍스트</p>
<p style="color: #2ecc71;">초록색 텍스트</p>
    `;
    
    window.editorcontent.setData(sampleContent);
    alert('✅ 샘플 내용이 설정되었습니다!');
}

function clearContent() {
    if (confirm('정말 내용을 모두 지우시겠습니까?')) {
        window.editorcontent.setData('');
        document.getElementById('resultBox').classList.remove('show');
        alert('✅ 내용이 지워졌습니다!');
    }
}
</script>

<?php include __DIR__ . '/../_footer.php'; ?>
