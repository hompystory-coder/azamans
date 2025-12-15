# 🚀 Phase 1 최종 배포 가이드

## 📋 배포 개요

**DDoS Security Platform Phase 1 (하이브리드 등록 시스템)**을 서버 `115.91.5.140`에 배포하는 가이드입니다.

---

## 🎯 배포 방법 (3가지 선택)

### ✅ 방법 1: 원라인 명령어 (추천)

서버 터미널에 직접 로그인하여 아래 명령어를 **전체 복사 후 실행**하세요:

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
echo "🔍 검증:" && \
curl -s http://localhost:3105/health && \
echo "" && \
curl -I https://ddos.neuralgrid.kr/register.html 2>&1 | head -1
```

---

### ✅ 방법 2: 원격 자동 배포 스크립트

GitHub에서 직접 다운로드하여 배포 (Git 없이도 가능):

```bash
curl -fsSL https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/remote-deploy.sh | bash
```

이 방법은:
- ✅ Git 없이도 배포 가능
- ✅ 자동 백업
- ✅ 자동 권한 설정
- ✅ PM2 자동 재시작
- ✅ 헬스 체크 자동 실행

---

### ✅ 방법 3: 수동 단계별 배포

```bash
# 1. Git 업데이트
cd /home/azamans/webapp
git fetch origin
git checkout genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean

# 2. 백업
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$(date +%Y%m%d-%H%M%S)

# 3. 파일 배포
sudo cp ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js
sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html

# 4. 권한 설정
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js

# 5. PM2 재시작
pm2 restart ddos-security

# 6. 검증
curl -s http://localhost:3105/health
curl -I https://ddos.neuralgrid.kr/register.html
```

---

## 🔍 배포 후 검증

### 1. API 서버 상태 확인
```bash
curl http://localhost:3105/health
# 예상 출력: {"status":"ok"}
```

### 2. PM2 프로세스 확인
```bash
pm2 status ddos-security
# 예상 출력: online, uptime > 0s
```

### 3. 등록 페이지 접근 테스트
```bash
curl -I https://ddos.neuralgrid.kr/register.html
# 예상 출력: HTTP/2 200
```

### 4. 브라우저에서 확인
- **등록 페이지**: https://ddos.neuralgrid.kr/register.html
- **메인 대시보드**: https://ddos.neuralgrid.kr/

---

## 📦 배포되는 파일

| 로컬 파일 | 서버 경로 | 설명 |
|----------|----------|------|
| `ddos-security-platform-server.js` | `/var/www/ddos.neuralgrid.kr/server.js` | Node.js Express 백엔드 API |
| `ddos-register.html` | `/var/www/ddos.neuralgrid.kr/register.html` | 서버 등록 UI |

---

## ✨ Phase 1 주요 기능

### 1. 서버 등록 시스템
- ✅ **무료 체험 플랜** (7일, 1대 서버)
- ✅ **프리미엄 플랜** (무제한 서버)
- ✅ SSO 통합 (neuralgrid.kr 자동 로그인)

### 2. API Key 자동 발급
```json
{
  "success": true,
  "server": {
    "serverId": "srv_1734307200123",
    "apiKey": "ngk_abc123xyz...",
    "installScript": "curl -fsSL https://ddos.neuralgrid.kr/install?key=ngk_abc123... | bash"
  }
}
```

### 3. 멀티 플랫폼 지원
- ✅ CentOS 7 (firewalld/iptables)
- ✅ Ubuntu/Debian (ufw/iptables)
- ✅ 자동 OS 감지

### 4. 방화벽 관리 API
- `POST /api/firewall/block` - IP 차단
- `POST /api/firewall/unblock` - IP 차단 해제
- `GET /api/firewall/list` - 차단 목록 조회
- `POST /api/firewall/domain-block` - 도메인 차단
- `POST /api/firewall/geo-block` - 국가 차단 (베타)

---

## 🛠️ 트러블슈팅

### PM2 프로세스가 시작되지 않는 경우
```bash
pm2 logs ddos-security --lines 50
pm2 restart ddos-security
```

### Nginx 404 에러
```bash
sudo nginx -t
sudo systemctl reload nginx
ls -la /var/www/ddos.neuralgrid.kr/
```

### 파일이 배포되지 않은 경우
```bash
ls -la /var/www/ddos.neuralgrid.kr/
# register.html과 server.js가 있는지 확인
```

### 권한 문제
```bash
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
sudo chmod 755 /var/www/ddos.neuralgrid.kr/
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html
```

### API가 응답하지 않는 경우
```bash
# 포트 확인
sudo netstat -tulpn | grep 3105

# PM2 재시작
pm2 restart ddos-security

# 로그 확인
pm2 logs ddos-security
```

---

## 📊 예상 사용자 플로우

```
1. neuralgrid.kr 로그인 (SSO)
      ↓
2. https://ddos.neuralgrid.kr/register.html 접속
      ↓
3. 무료 체험 or 프리미엄 선택
      ↓
4. 서버 정보 입력
   - 서버명 (예: "Production Server")
   - 서버 IP (예: "192.168.1.100")
   - 도메인 (예: "example.com")
   - OS 타입 (CentOS 7 / Ubuntu / Debian)
      ↓
5. "서버 등록" 버튼 클릭
      ↓
6. API Key 자동 발급
      ↓
7. 설치 스크립트 자동 생성
   curl -fsSL https://ddos.neuralgrid.kr/install?key=ngk_xxx | bash
      ↓
8. [다음 단계: 마이페이지에서 관리]
```

---

## 🎯 다음 단계 (Phase 2)

Phase 1 배포 완료 후:

1. **마이페이지 통합 대시보드** ⏳
   - 멀티 서버 관리
   - 실시간 통계 그래프
   - 차단 IP 목록

2. **실시간 모니터링 iframe** ⏳
   - 대시보드 임베드
   - Chart.js 실시간 차트

3. **서버 에이전트 개발** ⏳
   - 실제 동작하는 설치 스크립트
   - 서버 → API 데이터 전송

4. **관리자 승인 시스템** ⏳
   - 프리미엄 신청 승인
   - 이메일 알림

---

## 💾 Git 정보

- **브랜치**: `genspark_ai_developer_clean`
- **최신 커밋**: `27514f6`
- **저장소**: https://github.com/hompystory-coder/azamans
- **PR**: https://github.com/hompystory-coder/azamans/pull/1

---

## 📞 지원

배포 중 문제가 발생하면:
1. `pm2 logs ddos-security` 로그 확인
2. `/var/log/nginx/error.log` Nginx 에러 확인
3. GitHub Issues에 문의

---

**배포 일시**: 2025-12-15  
**작성자**: NeuralGrid AI Assistant  
**버전**: Phase 1 (Hybrid Registration System)
