# 🚀 대체 배포 방법 (SSH 접속 불가 시)

## 방법 1: 서버 터미널에서 직접 실행

서버 (115.91.5.140)에 **직접 로그인**하여 다음 명령어를 실행하세요:

```bash
# 1단계: Git에서 최신 코드 가져오기
cd /home/azamans/webapp
git fetch origin
git checkout genspark_ai_developer_clean
git pull origin genspark_ai_developer_clean

# 2단계: 파일 백업
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$(date +%Y%m%d-%H%M%S) 2>/dev/null

# 3단계: 파일 배포
sudo cp /home/azamans/webapp/ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js
sudo cp /home/azamans/webapp/ddos-register.html /var/www/ddos.neuralgrid.kr/register.html

# 4단계: 권한 설정
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html
sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js

# 5단계: PM2 재시작
pm2 restart ddos-security

# 6단계: 검증
curl http://localhost:3105/health
echo ""
curl -I https://ddos.neuralgrid.kr/register.html
```

## 방법 2: GitHub에서 직접 다운로드

```bash
# 서버에서 실행
cd /tmp
wget https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/ddos-security-platform-server.js
wget https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/ddos-register.html

# 백업
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$(date +%Y%m%d-%H%M%S)

# 배포
sudo cp ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js
sudo cp ddos-register.html /var/www/ddos.neuralgrid.kr/register.html
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/

# PM2 재시작
pm2 restart ddos-security

# 검증
curl http://localhost:3105/health
```

## 방법 3: 웹 인터페이스 (Webmin 등)

만약 Webmin이나 cPanel 같은 웹 관리 도구가 있다면:

1. 파일 관리자에서 `/var/www/ddos.neuralgrid.kr/` 이동
2. GitHub에서 파일 다운로드:
   - https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/ddos-security-platform-server.js
   - https://raw.githubusercontent.com/hompystory-coder/azamans/genspark_ai_developer_clean/ddos-register.html
3. 파일 업로드 및 이름 변경
4. 터미널에서 `pm2 restart ddos-security` 실행

## 배포 확인

배포 후 다음 URL에서 확인:

1. **등록 페이지**: https://ddos.neuralgrid.kr/register.html
2. **메인 대시보드**: https://ddos.neuralgrid.kr/
3. **API 헬스**: http://localhost:3105/health (서버 내부)

## 예상 결과

### 등록 페이지 (register.html)
- ✅ "무료 체험" vs "프리미엄 플랜" 선택 화면
- ✅ 서버 정보 입력 폼 (서버명, IP, 도메인, OS 타입)
- ✅ "서버 등록" 버튼
- ✅ SSO 토큰 자동 감지

### API 응답
```json
// POST /api/server/register 성공 시
{
  "success": true,
  "server": {
    "serverId": "srv_1734307200123",
    "apiKey": "ngk_abc123...",
    "installScript": "curl -fsSL https://ddos.neuralgrid.kr/install?key=ngk_abc123... | bash"
  }
}
```

## 트러블슈팅

### 1. 파일이 보이지 않는 경우
```bash
ls -la /var/www/ddos.neuralgrid.kr/
# register.html과 server.js가 있는지 확인
```

### 2. PM2 프로세스 에러
```bash
pm2 logs ddos-security --lines 50
pm2 restart ddos-security
```

### 3. Nginx 404 에러
```bash
sudo nginx -t
sudo systemctl reload nginx
```

### 4. 권한 문제
```bash
sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/
sudo chmod 755 /var/www/ddos.neuralgrid.kr/
sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html
```

---

**현재 상태**: SSH 접속 불가  
**해결 방법**: 서버 직접 로그인 또는 웹 인터페이스 사용  
**배포 파일**: Git Commit `9ad38d0` (genspark_ai_developer_clean)
