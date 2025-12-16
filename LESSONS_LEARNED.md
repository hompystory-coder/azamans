# 💡 배운 교훈: 웹 애플리케이션 배포 문제 해결

## 📅 날짜
2025-12-16

---

## 🔍 문제 상황

### 증상
- 서버에 파일을 배포했지만 브라우저에서 변경사항이 반영되지 않음
- 다른 PC에서도 동일한 문제 발생 (캐시 문제 아님)

### 초기 오판
❌ 브라우저 캐시 문제라고 생각함
- 시크릿 모드, 하드 리프레시 등 시도
- 캐시 삭제 가이드 작성
- **하지만 근본 원인이 아니었음!**

---

## ✅ 실제 원인

### 핵심 발견
웹 애플리케이션이 **정적 파일이 아니라 Node.js 서비스**로 제공됨!

### 확인 방법
```bash
# 1. Nginx 설정 확인
cat /etc/nginx/sites-enabled/auth.neuralgrid.kr

# 발견: proxy_pass http://127.0.0.1:3099
# → Nginx가 직접 서빙하는 것이 아님!

# 2. 프로세스 확인
pm2 list
pm2 info auth-service

# 발견: /home/azamans/n8n-neuralgrid/auth-service/
# → 실제 소스 코드 위치 발견!

# 3. 서버가 제공하는 파일 확인
curl -s https://auth.neuralgrid.kr/dashboard | grep "decodeURIComponent"
# → 0 (수정 코드 없음)

# 4. 실제 파일 확인
grep "decodeURIComponent" /var/www/auth.neuralgrid.kr/dashboard.html
# → 있음! (하지만 서빙되지 않음)
```

---

## 🎯 올바른 문제 해결 프로세스

### Step 1: 배포 아키텍처 파악 ⭐
**가장 먼저 해야 할 것!**

```bash
# Nginx 설정 확인
cat /etc/nginx/sites-enabled/[domain].conf

# 3가지 케이스:
# 1. 정적 파일 서빙: root /var/www/...
# 2. Node.js 프록시: proxy_pass http://127.0.0.1:port
# 3. PHP-FPM 등: fastcgi_pass ...
```

### Step 2: 실제 서빙되는 파일 확인
```bash
# 브라우저가 받는 파일 다운로드
curl -s https://domain.com/file.html -o /tmp/actual-file.html

# 수정사항 확인
grep "수정된내용" /tmp/actual-file.html

# 서버 파일과 비교
diff /var/www/.../file.html /tmp/actual-file.html
```

### Step 3: 프로세스 확인 (프록시인 경우)
```bash
# PM2 프로세스 확인
pm2 list
pm2 info [service-name]

# 실제 소스 코드 위치 확인
# → script path: /actual/path/to/service/

# 해당 위치에서 파일 확인
ls -la /actual/path/to/service/public/
```

### Step 4: 올바른 위치에 배포
```bash
# 백업
cp original.html original.html.backup-$(date +%Y%m%d-%H%M%S)

# 업데이트
cp updated-file.html /actual/path/to/service/public/file.html

# 프로세스 재시작
pm2 restart [service-name]
```

### Step 5: 배포 검증
```bash
# 즉시 확인
curl -s https://domain.com/file.html | grep "수정된내용"

# 브라우저 테스트
# - 시크릿 모드
# - F12 → Console 확인
```

---

## 📋 체크리스트: 배포 전 확인사항

### ✅ 필수 확인 (순서대로!)

1. **[ ] Nginx 설정 확인**
   ```bash
   cat /etc/nginx/sites-enabled/[domain].conf
   ```
   - 정적 파일인가? (root)
   - 프록시인가? (proxy_pass)
   - 포트 번호는?

2. **[ ] 실제 서빙 확인**
   ```bash
   curl -s https://domain.com/file.html -o /tmp/test.html
   grep "수정내용" /tmp/test.html
   ```

3. **[ ] 프로세스 확인 (프록시인 경우)**
   ```bash
   pm2 list
   pm2 info [service-name]
   lsof -i :[port]
   ```

4. **[ ] 실제 소스 위치 확인**
   ```bash
   # pm2 info에서 script path 확인
   ls -la [actual-source-path]/public/
   ```

5. **[ ] 올바른 위치에 배포**
   ```bash
   cp file.html [actual-source-path]/public/
   ```

6. **[ ] 프로세스 재시작**
   ```bash
   pm2 restart [service-name]
   ```

7. **[ ] 배포 검증**
   ```bash
   curl -s https://domain.com/file.html | grep "수정내용"
   ```

---

## 🚫 하지 말아야 할 것

### ❌ 잘못된 가정
1. **"브라우저 캐시 문제일 것이다"**
   - 다른 PC에서도 확인하기
   - 서버가 실제로 제공하는 파일 먼저 확인

2. **"/var/www/에만 배포하면 된다"**
   - Nginx 설정 먼저 확인
   - 프록시인 경우 다른 위치일 수 있음

3. **"파일 복사만 하면 된다"**
   - Node.js 서비스는 재시작 필요
   - PM2/Systemd/Docker 확인

---

## 🎯 Golden Rule

### 배포 문제 해결 순서

```
1. Nginx 설정 확인 (정적 vs 프록시)
   ↓
2. curl로 실제 서빙 파일 확인
   ↓
3. PM2/프로세스 확인 (프록시인 경우)
   ↓
4. 실제 소스 위치 찾기
   ↓
5. 올바른 위치에 배포
   ↓
6. 프로세스 재시작
   ↓
7. 배포 검증 (curl + 브라우저)
```

**캐시 의심은 마지막!**

---

## 📝 이번 케이스 요약

### 문제
- Cookie SSO 코드를 `/var/www/auth.neuralgrid.kr/`에 배포
- 변경사항이 반영되지 않음

### 원인
- Auth 서비스는 Node.js로 실행 중 (PM2)
- 실제 소스: `/home/azamans/n8n-neuralgrid/auth-service/public/`
- Nginx가 `http://127.0.0.1:3099`로 프록시

### 해결
1. 실제 소스 위치 발견
2. 올바른 위치에 파일 배포
3. `pm2 restart auth-service`
4. 배포 검증 완료

### 소요 시간
- 잘못된 접근: ~1시간 (캐시 문제로 착각)
- 올바른 해결: ~10분 (실제 원인 발견 후)

---

## 🔧 유용한 명령어 모음

### Nginx 관련
```bash
# 설정 파일 확인
cat /etc/nginx/sites-enabled/[domain].conf
ls -la /etc/nginx/sites-enabled/

# 설정 테스트
sudo nginx -t

# 리로드
sudo systemctl reload nginx
```

### PM2 관련
```bash
# 프로세스 목록
pm2 list

# 상세 정보
pm2 info [name]

# 재시작
pm2 restart [name]

# 로그 확인
pm2 logs [name] --lines 50
```

### 배포 검증
```bash
# 실제 서빙 파일 확인
curl -s https://domain.com/file.html -o /tmp/test.html
cat /tmp/test.html

# 특정 내용 검색
curl -s https://domain.com/file.html | grep "검색어"

# 파일 비교
diff file1.html file2.html
```

### 포트 확인
```bash
# 포트 사용 프로세스 확인
sudo lsof -i :3099
sudo netstat -tlnp | grep 3099
```

---

## 💡 핵심 교훈

### 1. 가정하지 말고 확인하라
- "캐시 문제일 것이다" ❌
- "서버에서 어떤 파일이 제공되는가?" ✅

### 2. 아키텍처를 먼저 파악하라
- Nginx 설정이 진실
- 정적 파일 vs 프록시 구분

### 3. 검증은 필수
- `curl`로 실제 서빙 파일 확인
- 브라우저 테스트 전에 서버 측 검증

### 4. 문서화하라
- Nginx 설정 문서화
- PM2 프로세스 매핑
- 배포 경로 명시

---

## 📚 참고 자료

### 배포 체크리스트
- [ ] Nginx 설정 확인
- [ ] 실제 서빙 파일 확인
- [ ] PM2 프로세스 확인
- [ ] 실제 소스 위치 확인
- [ ] 올바른 위치에 배포
- [ ] 프로세스 재시작
- [ ] 배포 검증

### 빠른 진단 스크립트
```bash
#!/bin/bash
# 배포 문제 진단 스크립트

DOMAIN="auth.neuralgrid.kr"
FILE="dashboard.html"

echo "=== Nginx 설정 확인 ==="
grep -A5 "server_name $DOMAIN" /etc/nginx/sites-enabled/*

echo ""
echo "=== 실제 서빙 파일 확인 ==="
curl -s https://$DOMAIN/$FILE | head -20

echo ""
echo "=== PM2 프로세스 확인 ==="
pm2 list | grep -i auth
```

---

**작성일**: 2025-12-16  
**작성자**: GenSpark AI Developer  
**케이스**: Cookie SSO 배포 문제 해결

**핵심**: 캐시가 아니라 배포 위치가 문제였다! 🎯
