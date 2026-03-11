# 🌐 shorts.neuralgrid.kr/azaman 경로 설정 가이드

## 📋 개요

`shorts.neuralgrid.kr` 사이트에 `/azaman/` 서브 경로를 추가하는 가이드입니다.

**기존 URL**: http://shorts.neuralgrid.kr/  
**새 URL**: http://shorts.neuralgrid.kr/azaman/

두 경로 모두 동일한 애플리케이션을 제공하지만, `/azaman/` 경로로 독립적으로 접근 가능합니다.

---

## 🚀 빠른 설치

### 자동 설치 스크립트 실행

```bash
cd /home/azamans/webapp/zero-install-ai-studio
sudo bash setup-azaman-path.sh
```

이 스크립트는 자동으로:
- ✅ Next.js basePath 설정 확인
- ✅ Nginx 설정 업데이트
- ✅ Next.js 애플리케이션 재빌드
- ✅ PM2 프로세스 재시작
- ✅ Nginx 재시작

---

## 🔧 수동 설정 (상세)

자동 스크립트를 사용하지 않는 경우:

### 1. Next.js basePath 설정

이미 적용되어 있습니다. `next.config.js` 확인:

```javascript
const nextConfig = {
  basePath: '/azaman',
  assetPrefix: '/azaman',
  // ... 기타 설정
}
```

### 2. Nginx 설정 업데이트

```bash
# 기존 설정 백업
sudo cp /etc/nginx/sites-available/shorts /etc/nginx/sites-available/shorts.backup

# 새 설정 복사
sudo cp /home/azamans/webapp/zero-install-ai-studio/nginx-shorts-azaman.conf \
  /etc/nginx/sites-available/shorts

# 설정 테스트
sudo nginx -t

# Nginx 재시작
sudo systemctl restart nginx
```

**Nginx 설정 내용**:
```nginx
server {
    listen 80;
    server_name shorts.neuralgrid.kr;
    
    # 기본 루트 경로
    location / {
        proxy_pass http://127.0.0.1:8080;
        # ... 헤더 설정
    }
    
    # /azaman/ 경로 (새로 추가)
    location /azaman/ {
        proxy_pass http://127.0.0.1:8080/azaman/;
        # ... 헤더 설정
    }
}
```

### 3. Next.js 재빌드

```bash
cd /home/azamans/webapp/zero-install-ai-studio

# 기존 빌드 삭제
rm -rf .next

# 의존성 설치 (필요시)
npm install

# 빌드 실행
npm run build
```

### 4. 애플리케이션 재시작

**PM2 사용 시:**
```bash
pm2 restart zero-install-ai-studio
# 또는
pm2 restart all
```

**Systemd 사용 시:**
```bash
sudo systemctl restart zero-install-ai-studio
```

---

## ✅ 접속 확인

### 1. HTTP 헤더 확인

```bash
# 기존 경로
curl -I http://shorts.neuralgrid.kr/

# 새 경로
curl -I http://shorts.neuralgrid.kr/azaman/
```

### 2. 브라우저 접속

**기존 경로:**
```
http://shorts.neuralgrid.kr/
```

**새 경로:**
```
http://shorts.neuralgrid.kr/azaman/
```

### 3. 정상 응답 확인

두 경로 모두 정상적으로 페이지가 로드되어야 합니다.

---

## 🔍 로그 확인

### Nginx 로그

```bash
# 에러 로그
sudo tail -f /var/log/nginx/error.log

# 액세스 로그
sudo tail -f /var/log/nginx/access.log
```

### Next.js/PM2 로그

```bash
# PM2 로그
pm2 logs zero-install-ai-studio

# PM2 상태 확인
pm2 status
```

### Systemd 로그 (사용 시)

```bash
sudo journalctl -u zero-install-ai-studio -f
```

---

## 🛠️ 문제 해결

### 404 Not Found 오류

**증상**: `/azaman/` 경로 접속 시 404 오류

**해결책**:
1. Next.js가 재빌드되었는지 확인
   ```bash
   ls -la /home/azamans/webapp/zero-install-ai-studio/.next
   ```
2. 빌드 재실행
   ```bash
   cd /home/azamans/webapp/zero-install-ai-studio
   rm -rf .next
   npm run build
   ```
3. 애플리케이션 재시작
   ```bash
   pm2 restart zero-install-ai-studio
   ```

### CSS/JS 파일 로드 실패

**증상**: 페이지는 로드되지만 스타일이 깨짐

**해결책**:
1. `next.config.js`에서 `assetPrefix` 확인
   ```javascript
   assetPrefix: '/azaman',
   ```
2. 브라우저 개발자 도구에서 네트워크 탭 확인
3. 경로가 `/azaman/_next/...`로 시작하는지 확인

### Nginx 502 Bad Gateway

**증상**: Nginx는 작동하지만 애플리케이션 연결 실패

**해결책**:
1. Next.js 애플리케이션이 실행 중인지 확인
   ```bash
   pm2 status
   # 또는
   lsof -i :8080
   ```
2. 애플리케이션 재시작
   ```bash
   pm2 restart zero-install-ai-studio
   ```

### 기존 경로도 작동하지 않음

**증상**: `/` 경로도 접속 불가

**해결책**:
1. Nginx 설정 롤백
   ```bash
   sudo cp /etc/nginx/sites-available/shorts.backup \
     /etc/nginx/sites-available/shorts
   sudo nginx -t
   sudo systemctl restart nginx
   ```
2. basePath 제거 후 재빌드
   ```javascript
   // next.config.js에서 basePath, assetPrefix 주석 처리
   npm run build
   pm2 restart zero-install-ai-studio
   ```

---

## 📊 설정 파일

### 생성된 파일 목록

```
zero-install-ai-studio/
├── next.config.js                    # basePath 설정 추가됨
├── nginx-shorts-azaman.conf          # Nginx 설정 파일
├── setup-azaman-path.sh             # 자동 설치 스크립트
└── AZAMAN_PATH_SETUP.md             # 이 가이드
```

---

## 🔄 설정 제거 (롤백)

`/azaman/` 경로를 제거하고 싶은 경우:

### 1. Next.js 설정 복원

`next.config.js`에서 다음 라인 제거:
```javascript
basePath: '/azaman',
assetPrefix: '/azaman',
```

### 2. Nginx 설정 복원

```bash
# 백업 복원
sudo cp /etc/nginx/sites-available/shorts.backup \
  /etc/nginx/sites-available/shorts

# Nginx 재시작
sudo systemctl restart nginx
```

### 3. Next.js 재빌드 및 재시작

```bash
cd /home/azamans/webapp/zero-install-ai-studio
rm -rf .next
npm run build
pm2 restart zero-install-ai-studio
```

---

## 📝 체크리스트

설정 완료 확인:

- [ ] next.config.js에 basePath 설정 확인
- [ ] Nginx 설정 업데이트 완료
- [ ] Next.js 빌드 완료 (.next 디렉토리 생성)
- [ ] 애플리케이션 재시작 완료
- [ ] Nginx 재시작 완료
- [ ] 기존 경로 접속 테스트 (/)
- [ ] 새 경로 접속 테스트 (/azaman/)
- [ ] CSS/JS 로드 확인
- [ ] 모든 기능 정상 작동 확인

---

## 🎯 활용 방안

### 사용 사례

1. **다른 사용자 전용 경로**: 특정 사용자나 팀에게 전용 URL 제공
2. **테스트 환경**: 프로덕션과 분리된 테스트 경로
3. **멀티 테넌트**: 여러 고객에게 개별 경로 제공
4. **A/B 테스팅**: 다른 설정으로 동작하는 경로 테스트

### 추가 경로 생성

다른 경로(예: `/another/`)를 추가하려면:

1. Nginx에 새 location 블록 추가
2. Next.js를 새 basePath로 별도 빌드 (또는 동적 처리)
3. 필요시 별도 포트로 실행

---

## 📞 지원

문제가 발생하면:

1. **로그 확인**: Nginx 및 애플리케이션 로그
2. **빌드 확인**: .next 디렉토리 생성 여부
3. **프로세스 확인**: 애플리케이션 실행 상태
4. **설정 확인**: next.config.js 및 Nginx 설정

---

**접속 URL**:
- 기존: http://shorts.neuralgrid.kr/
- 새로: http://shorts.neuralgrid.kr/azaman/

🎉 설정 완료!
