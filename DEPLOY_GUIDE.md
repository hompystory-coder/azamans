# 🚀 NeuralGrid 홈페이지 배포 가이드

## ⚠️ SSH 접속 문제

현재 SSH 접속이 불가능한 상태입니다:
- 서버 IP: 115.91.5.140
- 시도한 포트: 22, 2222, 2022, 22022, 22222
- 상태: Connection refused

**해결 방법**:
1. 서버의 SSH 포트 번호 확인
2. 방화벽 설정 확인
3. 직접 서버에 접속하여 배포

---

## 📦 배포할 파일

**파일명**: `neuralgrid-homepage.html`
**위치**: `/home/azamans/webapp/neuralgrid-homepage.html`
**크기**: 약 1500줄

---

## 🔧 수동 배포 방법

### 방법 1: 서버에서 직접 작업

```bash
# 1. 서버에 직접 접속 (콘솔 또는 KVM 사용)

# 2. nginx 웹 루트 확인
ls -la /var/www/html/
# 또는
ls -la /usr/share/nginx/html/

# 3. 기존 파일 백업
sudo cp /var/www/html/index.html /var/www/html/index.html.backup.$(date +%Y%m%d)

# 4. GitHub에서 최신 파일 가져오기
cd /tmp
git clone https://github.com/hompystory-coder/azamans.git
cd azamans
git checkout genspark_ai_developer_clean

# 5. 파일 복사
sudo cp neuralgrid-homepage.html /var/www/html/index.html

# 6. 권한 설정
sudo chown www-data:www-data /var/www/html/index.html
sudo chmod 644 /var/www/html/index.html

# 7. nginx 설정 확인
sudo nginx -t

# 8. nginx 재시작
sudo systemctl reload nginx

# 9. 확인
curl -I https://neuralgrid.kr/
```

### 방법 2: SCP로 파일 업로드 (SSH 포트 확인 후)

```bash
# SSH 포트를 알고 있다면
scp -P [포트번호] neuralgrid-homepage.html azamans@115.91.5.140:/tmp/

# 서버에서
sudo mv /tmp/neuralgrid-homepage.html /var/www/html/index.html
sudo systemctl reload nginx
```

### 방법 3: FTP/SFTP 사용

서버에 FTP/SFTP가 설정되어 있다면:
1. FileZilla 또는 WinSCP로 접속
2. `/var/www/html/` 디렉토리로 이동
3. `index.html` 백업
4. 새 파일 업로드

---

## ✅ 배포 후 확인사항

### 1. 웹사이트 접속 확인
```bash
curl -I https://neuralgrid.kr/
# HTTP/2 200 응답 확인
```

### 2. DDoS Tester 링크 확인
브라우저에서 https://neuralgrid.kr/ 접속 후:
- 스크롤하여 "🔧 추가 서비스" 섹션 찾기
- ⚡ DDoS Tester 카드가 표시되는지 확인
- Footer의 "리소스" 섹션에서 DDoS Tester 링크 확인

### 3. 링크 작동 확인
- DDoS Tester 카드 클릭 → https://ddos.neuralgrid.kr/ 로 이동
- Footer의 DDoS Tester 링크 클릭 → 정상 이동

---

## 🔍 트러블슈팅

### nginx 설정 파일 위치
```bash
# 메인 설정
/etc/nginx/nginx.conf

# 사이트별 설정
/etc/nginx/sites-available/
/etc/nginx/sites-enabled/

# neuralgrid.kr 설정 찾기
sudo grep -r "neuralgrid.kr" /etc/nginx/
```

### 웹 루트 위치 확인
```bash
# nginx 설정에서 root 디렉티브 찾기
sudo grep -r "root" /etc/nginx/sites-enabled/neuralgrid.kr

# 일반적인 위치:
# - /var/www/html/
# - /var/www/neuralgrid/
# - /usr/share/nginx/html/
# - /home/azamans/neuralgrid/
```

### 권한 문제
```bash
# nginx 사용자 확인
ps aux | grep nginx

# 파일 권한 설정
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/
sudo chmod 644 /var/www/html/index.html
```

### nginx 로그 확인
```bash
# 에러 로그
sudo tail -f /var/log/nginx/error.log

# 액세스 로그
sudo tail -f /var/log/nginx/access.log
```

---

## 📋 변경사항 요약

### 추가된 서비스
- **이름**: DDoS Tester
- **URL**: https://ddos.neuralgrid.kr
- **아이콘**: ⚡
- **한글명**: DDoS 부하 테스터
- **설명**: 웹사이트 내구성 테스트 도구

### 수정된 위치
1. **additionalServices 객체** (라인 ~1094-1110)
   - 'DDoS Tester' 항목 추가
   
2. **Footer 리소스 섹션** (라인 ~949)
   - DDoS Tester 링크 추가

---

## 🎯 완료 체크리스트

- [ ] 서버 SSH 접속 가능 확인
- [ ] nginx 웹 루트 디렉토리 확인
- [ ] 기존 index.html 백업
- [ ] neuralgrid-homepage.html → index.html 복사
- [ ] 파일 권한 설정 (644)
- [ ] nginx 설정 테스트 (nginx -t)
- [ ] nginx 재시작 (systemctl reload nginx)
- [ ] 웹사이트 접속 확인 (https://neuralgrid.kr/)
- [ ] DDoS Tester 카드 표시 확인
- [ ] DDoS Tester 링크 작동 확인
- [ ] Footer 링크 확인

---

## 📞 추가 지원

SSH 포트를 확인하거나 다른 방법이 필요한 경우:
1. 서버 관리자에게 SSH 포트 번호 문의
2. Cloudflare/DNS 설정 확인
3. 웹 호스팅 패널(cPanel, Plesk 등) 사용

---

**Git Commit**: dcee0db  
**Branch**: genspark_ai_developer_clean  
**PR**: https://github.com/hompystory-coder/azamans/pull/1

