# 🚀 Phase 1 배포 상태 및 실행 방법

## 📊 현재 상황

### ✅ 완료된 작업
- ✅ Phase 1 하이브리드 등록 시스템 개발 완료
- ✅ 서버 파일 준비 완료 (`ddos-security-platform-server.js`, `ddos-register.html`)
- ✅ Git 저장소에 커밋 및 푸시 완료 (브랜치: `genspark_ai_developer_clean`)
- ✅ 3가지 배포 방법 문서화 완료
- ✅ 자동 배포 스크립트 작성 완료

### ⚠️ 배포 차단 이유
- ❌ SSH 접속 불가 (포트 22, 2222, 2200, 22000, 8022 모두 차단됨)
- ⚠️ 현재 위치에서 서버 `115.91.5.140`에 직접 접근 불가

---

## 🎯 배포 실행 방법

서버 `115.91.5.140`에 **직접 접근 가능한 환경**에서 아래 방법 중 하나를 선택하여 실행하세요:

---

### 🥇 방법 1: 원라인 배포 명령어 (가장 추천)

서버 터미널에서 아래 명령어를 **전체 복사 후 실행**:

```bash
cd /home/azamans/webapp && \
git fetch origin && \
git checkout genspark_ai_developer_clean && \
git pull origin genspark_ai_developer_clean && \
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$(date +%Y%m%d-%H%M%S) 2>/dev/null && \
sudo cp ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js && \
sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html && \
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/ && \
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html && \
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js && \
pm2 restart ddos-security && \
sleep 2 && \
echo "" && \
echo "✅ 배포 완료!" && \
echo "" && \
curl -s http://localhost:3105/health && \
echo "" && \
curl -I https://ddos.neuralgrid.kr/register.html 2>&1 | head -1
```

**실행 시간**: 약 10초

---

### 🥈 방법 2: GitHub 직접 다운로드 배포

Git 없이도 배포 가능:

```bash
curl -fsSL https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/remote-deploy.sh | bash
```

이 방법은:
- ✅ Git 없이도 배포
- ✅ 자동 백업
- ✅ PM2 자동 재시작
- ✅ 헬스 체크 자동 실행

**실행 시간**: 약 15초

---

### 🥉 방법 3: 웹 인터페이스 배포

만약 웹 관리 도구가 있다면:

#### A. PHP 배포 스크립트 사용
1. `web-deploy.php` 파일을 `/var/www/html/` 에 업로드
2. 브라우저에서 접속:
   ```
   http://115.91.5.140/web-deploy.php?token=neuralgrid2025
   ```
3. 배포 진행 상황 확인

#### B. 수동 파일 업로드
1. GitHub에서 파일 다운로드:
   - https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/ddos-security-platform-server.js
   - https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/ddos-register.html

2. 웹 관리 도구에서 `/var/www/ddos.neuralgrid.kr/` 로 이동

3. 파일 업로드 및 이름 변경:
   - `ddos-security-platform-server.js` → `server.js`
   - `ddos-register.html` → `register.html`

4. 터미널에서 `pm2 restart ddos-security` 실행

---

## 🔍 배포 후 검증 방법

배포 완료 후 다음을 확인하세요:

### 1. 서버 내부 확인
```bash
# API 헬스 체크
curl http://localhost:3105/health
# 예상 출력: {"status":"ok"}

# PM2 상태
pm2 status ddos-security
# 예상: online, uptime > 0s

# 파일 확인
ls -la /var/www/ddos.neuralgrid.kr/
# register.html과 server.js가 있어야 함
```

### 2. 브라우저 확인
- **등록 페이지**: https://ddos.neuralgrid.kr/register.html
- **메인 대시보드**: https://ddos.neuralgrid.kr/

### 3. 예상 화면
등록 페이지에 다음이 표시되어야 합니다:
- ✅ "무료 체험" vs "프리미엄" 플랜 선택
- ✅ 서버 정보 입력 폼
- ✅ "서버 등록" 버튼

---

## 📦 배포되는 파일

| 파일 | 경로 | 크기 | 설명 |
|------|------|------|------|
| `ddos-security-platform-server.js` | `/var/www/ddos.neuralgrid.kr/server.js` | ~16KB | Node.js 백엔드 API |
| `ddos-register.html` | `/var/www/ddos.neuralgrid.kr/register.html` | ~26KB | 서버 등록 UI |

---

## 🛠️ 트러블슈팅

### PM2가 시작되지 않는 경우
```bash
pm2 logs ddos-security --lines 50
pm2 restart ddos-security
```

### 파일이 보이지 않는 경우
```bash
ls -la /var/www/ddos.neuralgrid.kr/
# register.html과 server.js 확인
```

### Nginx 404 에러
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 권한 문제
```bash
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html
```

---

## 📞 지원

배포 중 문제 발생 시:
1. PM2 로그 확인: `pm2 logs ddos-security`
2. Nginx 로그: `sudo tail -f /var/log/nginx/error.log`
3. 시스템 로그: `journalctl -xe`

---

## 📋 배포 체크리스트

배포 전:
- [ ] 서버 `115.91.5.140`에 터미널 접속 가능 확인
- [ ] Git 저장소 접근 가능 확인 (`/home/azamans/webapp`)
- [ ] PM2가 설치되어 있고 `ddos-security` 프로세스 존재 확인

배포 중:
- [ ] 방법 1, 2, 3 중 하나 선택
- [ ] 배포 명령어 실행
- [ ] 에러 없이 완료 확인

배포 후:
- [ ] API 헬스 체크: `curl http://localhost:3105/health`
- [ ] PM2 상태: `pm2 status ddos-security`
- [ ] 브라우저 접속: https://ddos.neuralgrid.kr/register.html
- [ ] 등록 폼이 정상적으로 표시되는지 확인

---

## 💾 Git 정보

- **브랜치**: `genspark_ai_developer_clean`
- **최신 커밋**: `dd8d3e1`
- **저장소**: https://github.com/hompystory-coder/azamans
- **PR**: https://github.com/hompystory-coder/azamans/pull/1

---

## 🎯 다음 단계

배포 완료 및 테스트 후:
1. Phase 2: 마이페이지 통합 대시보드 개발
2. Phase 3: 실시간 모니터링 시스템
3. Phase 4: 서버 에이전트 개발

---

**배포 준비 완료 일시**: 2025-12-15  
**배포 대기 상태**: 서버 직접 접속 필요  
**예상 배포 시간**: 10~15초
