# auth.neuralgrid.kr DNS 설정 가이드

## 📋 필요한 DNS 설정

Cloudflare 대시보드에서 다음 DNS 레코드를 추가해주세요:

### DNS 레코드 정보
```
Type: A
Name: auth
Content: 115.91.5.140
Proxy status: Proxied (오렌지 구름 ON)
TTL: Auto
```

## 🔧 Cloudflare 설정 방법

1. https://dash.cloudflare.com 접속
2. neuralgrid.kr 도메인 선택
3. DNS → Records 메뉴
4. "Add record" 버튼 클릭
5. 위 정보 입력
6. Save 클릭

## ✅ 설정 확인 방법

DNS 전파 후 (5-10분) 확인:
```bash
dig +short auth.neuralgrid.kr
# 출력: 115.91.5.140
```

## 🔐 SSL 인증서 발급 (DNS 설정 후)

```bash
sudo certbot certonly --nginx -d auth.neuralgrid.kr \
  --non-interactive --agree-tos -m admin@neuralgrid.kr
```

## 📝 Nginx 설정 업데이트

```bash
# Update SSL certificate path
sudo nano /etc/nginx/sites-available/auth.neuralgrid.kr

# Change:
ssl_certificate /etc/letsencrypt/live/neuralgrid.kr/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/neuralgrid.kr/privkey.pem;

# To:
ssl_certificate /etc/letsencrypt/live/auth.neuralgrid.kr/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/auth.neuralgrid.kr/privkey.pem;

# Reload Nginx
sudo systemctl reload nginx
```

---

**현재 상태**: DNS 레코드 추가 대기 중
**예상 소요 시간**: 약 15분 (DNS 전파 포함)
