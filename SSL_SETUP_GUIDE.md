# SSL 인증서 발급 가이드

## 🔒 HTTPS 연결 문제 해결

현재 `https://mvc.neuralgrid.kr`에 "연결이 비공개로 설정되어 있습니다" 오류가 발생하는 이유는 
SSL 인증서가 아직 발급되지 않았기 때문입니다.

---

## 해결 방법 1: SSL 인증서 발급 (권장)

다음 명령어를 **root 권한**으로 실행하세요:

```bash
sudo /tmp/setup_ssl_mvc.sh
```

또는 수동으로:

```bash
sudo certbot --nginx -d mvc.neuralgrid.kr --non-interactive --agree-tos --email admin@neuralgrid.kr --redirect
```

### 발급 후 확인
```bash
# Nginx 재시작
sudo systemctl reload nginx

# SSL 인증서 확인
sudo certbot certificates
```

---

## 해결 방법 2: 임시로 HTTP 사용

SSL 인증서 발급 전까지 HTTP로 접속하려면:

### HTTP로 접속
```
http://mvc.neuralgrid.kr
```

⚠️ **주의**: HTTP는 암호화되지 않으므로 프로덕션 환경에서는 사용하지 마세요.

---

## 자동 갱신 설정 확인

SSL 인증서는 90일마다 갱신해야 합니다. 자동 갱신이 설정되어 있는지 확인:

```bash
sudo systemctl status certbot.timer
```

---

## 문제 해결

### 1. 포트 확인
```bash
# 80번 포트 (HTTP)
sudo netstat -tlnp | grep :80

# 443번 포트 (HTTPS)
sudo netstat -tlnp | grep :443
```

### 2. 방화벽 확인
```bash
# UFW 사용 시
sudo ufw status
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

### 3. DNS 확인
```bash
# 도메인이 올바른 IP를 가리키는지 확인
nslookup mvc.neuralgrid.kr
```

---

## 기존 서브도메인 인증서 확인

다음 neuralgrid.kr 서브도메인들은 이미 SSL 인증서가 발급되어 있습니다:
- route.neuralgrid.kr
- news.neuralgrid.kr
- market.neuralgrid.kr
- shorts.neuralgrid.kr
- ollama.neuralgrid.kr
- n8n.neuralgrid.kr
- monitor.neuralgrid.kr
- auth.neuralgrid.kr
- ddos.neuralgrid.kr
- ai.neuralgrid.kr

같은 방식으로 mvc.neuralgrid.kr도 발급 가능합니다.

---

## 빠른 해결

가장 빠른 방법:

```bash
# 1. SSL 인증서 발급
sudo certbot --nginx -d mvc.neuralgrid.kr

# 2. Nginx 재시작
sudo systemctl reload nginx

# 3. 접속 확인
curl -I https://mvc.neuralgrid.kr
```

완료되면 `https://mvc.neuralgrid.kr`로 정상 접속이 가능합니다! 🎉

---

## 참고

- Let's Encrypt는 무료 SSL 인증서를 제공합니다
- 인증서는 90일마다 자동 갱신됩니다
- certbot.timer 서비스가 자동 갱신을 담당합니다
