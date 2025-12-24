# Nginx 설정: https://shorts.neuralgrid.kr/preview 활성화

## 문제
현재 `https://shorts.neuralgrid.kr/preview`가 작동하지 않음
- IP 주소로는 접속 가능: `http://115.91.5.140:3003/preview`
- HTTPS 도메인으로는 접속 불가: `https://shorts.neuralgrid.kr/preview`

## 원인
Nginx가 `/preview` 경로를 프록시하지 않음
- 기본 경로 `/`는 포트 3006으로 프록시 (프론트엔드)
- `/api/`는 포트 4001로 프록시 (백엔드)
- `/preview`는 **설정되지 않음** → 포트 3003 필요

## 해결 방법

### 1단계: Nginx 설정 파일 열기
```bash
sudo nano /etc/nginx/sites-enabled/shorts.neuralgrid.kr
```

### 2단계: 다음 설정을 추가

**위치**: `# API 요청을 백엔드(4001)로 프록시` 섹션 **바로 위**에 추가

```nginx
# Preview 페이지를 shorts-market (3003)로 프록시
location /preview {
    proxy_pass http://127.0.0.1:3003/preview;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;
}

# /api/preview/* API를 shorts-market (3003)로 프록시
location /api/preview/ {
    proxy_pass http://127.0.0.1:3003/api/preview/;
    proxy_http_version 1.1;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 600s;
    proxy_connect_timeout 600s;
    proxy_send_timeout 600s;
}
```

### 3단계: 설정 확인
```bash
sudo nginx -t
```

예상 출력:
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### 4단계: Nginx 재시작
```bash
sudo systemctl reload nginx
# 또는
sudo nginx -s reload
```

### 5단계: 테스트
```bash
curl -I https://shorts.neuralgrid.kr/preview
```

예상 출력:
```
HTTP/2 200
```

## 설정 파일 위치

전체 설정 내용은 다음 파일에 저장되어 있습니다:
```
/tmp/nginx_preview_config.txt
```

## 적용 후 접속

### HTTPS (권장)
```
https://shorts.neuralgrid.kr/preview
```

### HTTP (IP)
```
http://115.91.5.140:3003/preview
```

## 설정 예시 (전체 파일)

```nginx
server {
    listen 443 ssl http2;
    server_name shorts.neuralgrid.kr;
    
    ssl_certificate /etc/letsencrypt/live/shorts.neuralgrid.kr/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/shorts.neuralgrid.kr/privkey.pem;
    
    # ✅ 새로 추가: Preview 페이지
    location /preview {
        proxy_pass http://127.0.0.1:3003/preview;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
    
    # ✅ 새로 추가: Preview API
    location /api/preview/ {
        proxy_pass http://127.0.0.1:3003/api/preview/;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
    }
    
    # 기존: API 요청 (4001)
    location /api/ {
        proxy_pass http://127.0.0.1:4001;
        proxy_http_version 1.1;
        # ... (기존 설정)
    }
    
    # 기존: 프론트엔드 (3006)
    location / {
        proxy_pass http://127.0.0.1:3006;
        proxy_http_version 1.1;
        # ... (기존 설정)
    }
}
```

## 트러블슈팅

### 문제: 502 Bad Gateway
**원인**: 포트 3003이 열리지 않음
**해결**:
```bash
pm2 status shorts-market
pm2 restart shorts-market
ss -tlnp | grep 3003
```

### 문제: 404 Not Found
**원인**: Nginx 설정이 적용되지 않음
**해결**:
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 문제: Permission Denied
**원인**: sudo 권한 필요
**해결**:
```bash
sudo -i
# 그 다음 설정 파일 수정
```

## 완료 확인

설정이 완료되면:
1. ✅ `https://shorts.neuralgrid.kr/preview` 접속 가능
2. ✅ 76개 영상 표시
3. ✅ 캐릭터 필터 작동
4. ✅ SSL 인증서 적용 (🔒 자물쇠 표시)

---

**작성일**: 2025-12-24
**관련 파일**: `/etc/nginx/sites-enabled/shorts.neuralgrid.kr`
**필요 권한**: sudo
