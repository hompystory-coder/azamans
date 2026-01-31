# Nginx 라우팅 문제 해결 가이드

## 🚨 문제 상황

다음 페이지들에 접근할 수 없는 문제:
- https://mvc.neuralgrid.kr/member/login
- https://mvc.neuralgrid.kr/member/register  
- https://mvc.neuralgrid.kr/admin

**원인**: Nginx의 `try_files` 설정이 URL 파라미터를 올바르게 전달하지 못함

---

## ✅ 해결 방법

다음 명령어를 **root 권한으로** 실행하세요:

```bash
sudo /tmp/fix_nginx_routing.sh
```

이 스크립트는:
1. 기존 Nginx 설정을 백업합니다
2. 올바른 라우팅 규칙을 적용합니다
3. Nginx를 재시작합니다

---

## 🔧 수동 수정 방법

### 1. Nginx 설정 파일 편집

```bash
sudo nano /etc/nginx/sites-available/mvc.neuralgrid.kr
```

### 2. `location /` 블록을 다음과 같이 수정

**변경 전:**
```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

**변경 후:**
```nginx
location / {
    # 실제 파일이나 디렉토리가 없으면 index.php로 라우팅
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?url=$1 last;
    }
}
```

### 3. Nginx 설정 테스트

```bash
sudo nginx -t
```

### 4. Nginx 재시작

```bash
sudo systemctl reload nginx
```

---

## 📝 작동 원리

### MVC 라우팅 구조

1. **요청**: `https://mvc.neuralgrid.kr/member/login`
2. **Nginx**: 실제 파일이 없으면 → `/index.php?url=member/login`로 rewrite
3. **index.php**: `$_GET['url']` = `member/login`
4. **Application 클래스**: URL을 파싱
   - Controller: `member`
   - Method: `login`
5. **실행**: `Member` 클래스의 `login()` 메서드 호출

---

## 🧪 테스트

설정 변경 후 다음 URL들을 테스트하세요:

```bash
# 로그인 페이지
curl -I https://mvc.neuralgrid.kr/member/login

# 회원가입 페이지
curl -I https://mvc.neuralgrid.kr/member/register

# 관리자 페이지
curl -I https://mvc.neuralgrid.kr/admin
```

모두 `HTTP/2 200` 응답이 나와야 합니다.

---

## 🔍 문제가 계속되면

### 1. Nginx 에러 로그 확인

```bash
sudo tail -50 /var/log/nginx/mvc_error.log
```

### 2. PHP-FPM 에러 로그 확인

```bash
sudo tail -50 /var/log/php8.3-fpm.log
```

### 3. 권한 확인

```bash
# /home/mvc 디렉토리 권한
ls -la /home/mvc

# Nginx가 읽을 수 있는지 확인
sudo -u www-data test -r /home/mvc/index.php && echo "OK" || echo "Permission denied"
```

### 4. PHP-FPM 소켓 확인

```bash
ls -la /run/php/php8.3-fpm.sock
```

---

## 📊 현재 상태 확인

```bash
# Nginx 상태
sudo systemctl status nginx

# PHP-FPM 상태
sudo systemctl status php8.3-fpm

# 포트 확인
sudo netstat -tlnp | grep nginx
```

---

## 🎯 빠른 해결 (요약)

```bash
# 1. 스크립트 실행
sudo /tmp/fix_nginx_routing.sh

# 2. 테스트
curl -I https://mvc.neuralgrid.kr/member/login

# 3. 브라우저에서 확인
# https://mvc.neuralgrid.kr/member/login
```

완료! 🎉

---

## 💡 추가 팁

### URL 디버깅

`/home/mvc/debug.php` 파일을 만들어 URL 전달을 확인할 수 있습니다:

```php
<?php
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "GET url: " . ($_GET['url'] ?? 'not set') . "\n";
print_r($_GET);
```

접속: `https://mvc.neuralgrid.kr/debug.php`

---

## 🔒 보안 참고사항

- `.env` 파일 접근 차단 확인
- `application/config/` 디렉토리 접근 차단
- `.sql`, `.db` 파일 접근 차단
- 숨김 파일 (`.git`, `.htaccess`) 접근 차단

모두 Nginx 설정에 포함되어 있습니다.
