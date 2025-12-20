# 🔐 AI Shorts Pro - SSL Certificate 문제 해결

## 🚨 현재 문제

SSL 인증서가 `ai-shorts.neuralgrid.kr` 도메인을 포함하지 않아서 HTTPS 접속이 안 됩니다.

```
curl: (60) SSL: no alternative certificate subject name matches target host name 'ai-shorts.neuralgrid.kr'
```

---

## ✅ 해결 방법 (2가지 옵션)

### **Option 1: SSL 인증서 발급 (추천) 🌟**

Let's Encrypt를 사용하여 `ai-shorts.neuralgrid.kr` 도메인에 SSL 인증서를 발급받습니다.

```bash
sudo bash /home/azamans/webapp/fix-ssl-ai-shorts.sh
```

**이 스크립트는:**
1. Certbot으로 SSL 인증서 발급
2. Nginx 설정 자동 업데이트
3. Nginx 재시작
4. HTTPS 접속 가능하게 설정

**결과:**
- ✅ `https://ai-shorts.neuralgrid.kr` 접속 가능
- ✅ 안전한 HTTPS 연결
- ✅ 브라우저 경고 없음

---

### **Option 2: HTTP만 사용 (임시 방편)**

SSL 인증서 없이 HTTP로만 접속하도록 설정합니다.

```bash
sudo bash /home/azamans/webapp/use-http-only.sh
```

**이 스크립트는:**
1. HTTPS 리다이렉트 제거
2. HTTP-only 설정으로 변경
3. Nginx 재시작

**결과:**
- ✅ `http://ai-shorts.neuralgrid.kr` 접속 가능
- ⚠️ 보안 연결 없음 (개발/테스트용)
- ⚠️ 브라우저에서 "안전하지 않음" 표시

---

## 🎯 권장 사항

### **프로덕션 배포: Option 1 사용**

```bash
sudo bash /home/azamans/webapp/fix-ssl-ai-shorts.sh
```

SSL 인증서는 무료이고 자동으로 갱신되므로, 프로덕션 환경에서는 반드시 사용하세요.

### **빠른 테스트: Option 2 사용**

```bash
sudo bash /home/azamans/webapp/use-http-only.sh
```

일단 HTTP로 빠르게 테스트하고, 나중에 Option 1로 SSL을 추가할 수 있습니다.

---

## 📋 단계별 가이드

### **Step 1: SSL 인증서 발급 시도**

```bash
sudo bash /home/azamans/webapp/fix-ssl-ai-shorts.sh
```

### **Step 2: 접속 확인**

```bash
# HTTPS 접속 테스트
curl -I https://ai-shorts.neuralgrid.kr

# API 헬스체크
curl https://ai-shorts.neuralgrid.kr/api/health
```

### **Step 3: 브라우저에서 접속**

https://ai-shorts.neuralgrid.kr

---

## 🔍 문제 해결

### **SSL 인증서 발급 실패 시**

```bash
# 원인 확인
sudo certbot certificates

# DNS 확인
nslookup ai-shorts.neuralgrid.kr

# 80 포트 확인
sudo netstat -tulpn | grep :80
```

**가능한 원인:**
1. DNS가 아직 전파되지 않음 (24-48시간 소요)
2. 포트 80이 방화벽에 막혀있음
3. Certbot이 설치되지 않음

**해결:**
- DNS 전파 대기: https://dnschecker.org
- 방화벽 확인: `sudo ufw status`
- Certbot 설치: `sudo apt install certbot python3-certbot-nginx`

---

## 🚀 빠른 임시 해결책

DNS 전파를 기다리는 동안 HTTP로 먼저 테스트:

```bash
# HTTP-only 모드로 전환
sudo bash /home/azamans/webapp/use-http-only.sh

# 접속
http://ai-shorts.neuralgrid.kr
```

---

## 📊 현재 상태

✅ Backend: 실행 중 (Port 5555)  
✅ Frontend: 빌드 완료  
✅ Nginx: 설정 완료  
⚠️ SSL: 인증서 필요  

---

## 🎯 다음 단계

1. **SSL 발급 시도**
   ```bash
   sudo bash /home/azamans/webapp/fix-ssl-ai-shorts.sh
   ```

2. **실패 시 HTTP 사용**
   ```bash
   sudo bash /home/azamans/webapp/use-http-only.sh
   ```

3. **접속 확인**
   - HTTPS: https://ai-shorts.neuralgrid.kr
   - HTTP: http://ai-shorts.neuralgrid.kr

---

## 📞 추가 정보

- **기존 인증서**: `/etc/letsencrypt/live/neuralgrid.kr/`
- **새 인증서 위치**: `/etc/letsencrypt/live/ai-shorts.neuralgrid.kr/`
- **Nginx 설정**: `/etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf`
- **백업 설정**: `/etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf.backup`

---

**선택하세요:**

1️⃣ **프로덕션 배포 (HTTPS)**: `sudo bash /home/azamans/webapp/fix-ssl-ai-shorts.sh`

2️⃣ **빠른 테스트 (HTTP)**: `sudo bash /home/azamans/webapp/use-http-only.sh`
