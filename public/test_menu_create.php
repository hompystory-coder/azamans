<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>메뉴 생성 테스트</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>메뉴 생성 디버그 테스트</h2>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>1. 직접 POST 테스트</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/createMenu">
                    <div class="mb-3">
                        <label>메뉴명:</label>
                        <input type="text" name="menu_name" class="form-control" value="테스트메뉴" required>
                    </div>
                    <button type="submit" class="btn btn-primary">직접 POST 제출</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>2. AJAX 테스트</h5>
            </div>
            <div class="card-body">
                <button id="testAjax" class="btn btn-success">AJAX로 메뉴 생성 테스트</button>
                <div id="result" class="mt-3"></div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>3. URL 테스트</h5>
            </div>
            <div class="card-body">
                <button id="testUrl" class="btn btn-info">URL 접근 테스트</button>
                <pre id="urlResult" class="mt-3 bg-light p-3"></pre>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>4. 현재 메뉴 목록</h5>
            </div>
            <div class="card-body">
                <button id="showMenus" class="btn btn-warning">메뉴 목록 조회</button>
                <div id="menuList" class="mt-3"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // AJAX 테스트
        $('#testAjax').click(function() {
            $('#result').html('<div class="alert alert-info">요청 중...</div>');
            
            $.ajax({
                url: '/admin/createMenu',
                method: 'POST',
                data: { menu_name: 'AJAX테스트메뉴' },
                dataType: 'json',
                success: function(response) {
                    console.log('Success:', response);
                    $('#result').html('<div class="alert alert-success"><strong>성공!</strong><pre>' + JSON.stringify(response, null, 2) + '</pre></div>');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    $('#result').html(
                        '<div class="alert alert-danger">' +
                        '<strong>오류 발생!</strong><br>' +
                        'Status: ' + xhr.status + '<br>' +
                        'Status Text: ' + xhr.statusText + '<br>' +
                        'Ready State: ' + xhr.readyState + '<br>' +
                        '<strong>Response:</strong><pre>' + xhr.responseText + '</pre>' +
                        '</div>'
                    );
                }
            });
        });

        // URL 테스트
        $('#testUrl').click(function() {
            $('#urlResult').text('요청 중...');
            
            fetch('/admin/createMenu', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'menu_name=Fetch테스트메뉴'
            })
            .then(response => {
                console.log('Response:', response);
                return response.text();
            })
            .then(text => {
                console.log('Text:', text);
                $('#urlResult').text(text);
            })
            .catch(error => {
                console.error('Error:', error);
                $('#urlResult').text('Error: ' + error.message);
            });
        });

        // 메뉴 목록 조회
        $('#showMenus').click(function() {
            $('#menuList').html('<div class="alert alert-info">조회 중...</div>');
            
            $.ajax({
                url: '/admin/menu/header',
                method: 'GET',
                success: function(response) {
                    $('#menuList').html('<div class="alert alert-success">페이지 로드 성공 (브라우저에서 확인)</div>');
                },
                error: function(xhr) {
                    $('#menuList').html('<div class="alert alert-danger">오류: ' + xhr.status + '</div>');
                }
            });
        });
    });
    </script>
</body>
</html>
