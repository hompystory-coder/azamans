# DDoS Security Platform Phase 1 배포 가이드

## 🚀 배포 방법

### 서버 (115.91.5.140)에서 직접 실행:

```bash
# 1. Git 저장소에서 최신 코드 가져오기
cd /home/azamans/webapp
git fetch origin
git checkout genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean

# 2. 파일 백업
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$(date +%Y%m%d-%H%M%S)

# 3. 새 파일 배포
sudo cp ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js
sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html

# 4. 권한 설정
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js

# 5. PM2 재시작
pm2 restart ddos-security

# 6. Nginx 재로드
sudo nginx -t
sudo systemctl reload nginx

# 7. 동작 확인
curl http://localhost:3105/health
curl -I https://ddos.neuralgrid.kr/
curl -I https://ddos.neuralgrid.kr/register.html
```

## 📦 배포된 파일

1. **ddos-security-platform-server.js** → `/var/www/ddos.neuralgrid.kr/server.js`
   - SSO 통합 서버 등록 API
   - API Key 자동 발급
   - 방화벽 관리 API (iptables/firewalld/ufw)
   - 설치 스크립트 생성기

2. **ddos-register.html** → `/var/www/ddos.neuralgrid.kr/register.html`
   - 무료 체험 vs. 프리미엄 플랜 선택 UI
   - 서버 등록 폼
   - 설치 스크립트 다운로드

## 🔍 확인 URL

- **메인 대시보드**: https://ddos.neuralgrid.kr/
- **서버 등록 페이지**: https://ddos.neuralgrid.kr/register.html
- **API 헬스 체크**: http://localhost:3105/health

## 📋 주요 API 엔드포인트

### 서버 등록
```bash
POST /api/server/register
Content-Type: application/json
Authorization: Bearer {jwt_token}

{
  "serverName": "My Server",
  "serverIp": "192.168.1.100",
  "domain": "example.com",
  "osType": "ubuntu",
  "plan": "trial" // or "premium"
}
```

### 설치 스크립트 다운로드
```bash
GET /install?key={api_key}
```

### 방화벽 관리
```bash
POST /api/firewall/block
POST /api/firewall/unblock
GET /api/firewall/list
POST /api/firewall/domain-block
POST /api/firewall/geo-block
```

## ✅ 배포 후 테스트

1. **등록 페이지 접근 테스트**
   ```bash
   curl -I https://ddos.neuralgrid.kr/register.html
   # Expected: HTTP/2 200
   ```

2. **API 헬스 체크**
   ```bash
   curl http://localhost:3105/health
   # Expected: {"status":"ok"}
   ```

3. **PM2 상태 확인**
   ```bash
   pm2 status ddos-security
   # Expected: online, uptime > 0s
   ```

## 🔧 트러블슈팅

### PM2 프로세스가 시작되지 않는 경우
```bash
pm2 logs ddos-security --lines 50
pm2 restart ddos-security
```

### Nginx 에러 발생 시
```bash
sudo nginx -t
sudo systemctl status nginx
sudo tail -f /var/log/nginx/error.log
```

### 포트 충돌 시
```bash
sudo netstat -tulpn | grep 3105
pm2 delete ddos-defense  # 이전 프로세스 제거
pm2 restart ddos-security
```

## 📊 다음 단계 (Phase 2)

- [ ] 마이페이지 통합 대시보드 개발
- [ ] 실시간 모니터링 iframe 통합
- [ ] 서버 에이전트 개발
- [ ] 멀티 서버 관리 UI
- [ ] 관리자 승인 워크플로우

---

**배포 일시**: 2025-12-15  
**Git Commit**: [최신 커밋 해시]  
**Branch**: genspark_ai_developer_clean  
**Repository**: https://github.com/hompystory-coder/azamans
