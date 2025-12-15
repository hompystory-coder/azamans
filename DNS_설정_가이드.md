# 🌐 NeuralGrid DNS 설정 가이드

## 📋 Cloudflare DNS 레코드 추가

### 서버 정보
- **서버 IP**: `115.91.5.140`
- **도메인**: `neuralgrid.kr`
- **Cloudflare 대시보드**: https://dash.cloudflare.com

---

## 🆕 신규 서브도메인 (설정 필요)

### ai.neuralgrid.kr (AnythingLLM - AI 어시스턴트)

**서비스 정보:**
- **서비스명**: AnythingLLM (개인 LLM 플랫폼)
- **포트**: 3104
- **용도**: RAG 기반 문서 AI 채팅

**DNS 레코드 설정:**
```
유형 (Type): A
이름 (Name): ai
IPv4 주소 (IPv4 address): 115.91.5.140
프록시 상태 (Proxy status): ✅ 프록시됨 (주황색 구름)
TTL: 자동 (Auto)
```

**Cloudflare 설정 방법:**
1. https://dash.cloudflare.com 로그인
2. `neuralgrid.kr` 도메인 선택
3. **DNS** 메뉴 클릭
4. **레코드 추가** 버튼 클릭
5. 위 정보 입력
6. **저장** 클릭

---

## ✅ 기존 운영 중인 서브도메인 (참고용)

### 1. neuralgrid.kr (메인 플랫폼)
```
Type: A
Name: @ (루트)
IPv4: 115.91.5.140
Proxy: ✅ ON
Status: 🟢 운영 중
```

### 2. auth.neuralgrid.kr (인증 허브)
```
Type: A
Name: auth
IPv4: 115.91.5.140
Proxy: ✅ ON
Port: 3099
Status: 🟢 운영 중
```

### 3. bn-shop.neuralgrid.kr (블로그 숏츠)
```
Type: A
Name: bn-shop
IPv4: 115.91.5.140
Proxy: ✅ ON
Status: 🟢 운영 중
```

### 4. mfx.neuralgrid.kr (MediaFX)
```
Type: A
Name: mfx
IPv4: 115.91.5.140
Proxy: ✅ ON
Status: 🟢 운영 중
```

### 5. music.neuralgrid.kr (StarMusic)
```
Type: A
Name: music
IPv4: 115.91.5.140
Proxy: ✅ ON
Status: 🟢 운영 중
```

### 6. market.neuralgrid.kr (쿠팡 숏츠)
```
Type: A
Name: market
IPv4: 115.91.5.140
Proxy: ✅ ON
Status: 🟢 운영 중
```

### 7. n8n.neuralgrid.kr (N8N 자동화)
```
Type: A
Name: n8n
IPv4: 115.91.5.140
Proxy: ✅ ON
Status: 🟢 운영 중
```

### 8. monitor.neuralgrid.kr (서버 모니터)
```
Type: A
Name: monitor
IPv4: 115.91.5.140
Proxy: ✅ ON
Status: 🟢 운영 중
```

---

## 📊 DNS 설정 요약

### 설정해야 할 신규 레코드: 1개

| 서브도메인 | Type | Name | IPv4 | Proxy | 서비스 | 포트 | 상태 |
|-----------|------|------|------|-------|--------|------|------|
| **ai.neuralgrid.kr** | **A** | **ai** | **115.91.5.140** | **✅ ON** | **AnythingLLM** | **3104** | **🟡 대기** |

### 기존 운영 중인 레코드: 8개

| 서브도메인 | Type | Name | IPv4 | Proxy | 상태 |
|-----------|------|------|------|-------|------|
| neuralgrid.kr | A | @ | 115.91.5.140 | ✅ | 🟢 |
| auth.neuralgrid.kr | A | auth | 115.91.5.140 | ✅ | 🟢 |
| bn-shop.neuralgrid.kr | A | bn-shop | 115.91.5.140 | ✅ | 🟢 |
| mfx.neuralgrid.kr | A | mfx | 115.91.5.140 | ✅ | 🟢 |
| music.neuralgrid.kr | A | music | 115.91.5.140 | ✅ | 🟢 |
| market.neuralgrid.kr | A | market | 115.91.5.140 | ✅ | 🟢 |
| n8n.neuralgrid.kr | A | n8n | 115.91.5.140 | ✅ | 🟢 |
| monitor.neuralgrid.kr | A | monitor | 115.91.5.140 | ✅ | 🟢 |

---

## 🚀 DNS 설정 후 할 일

### 1단계: DNS 전파 확인 (1-5분)

```bash
# DNS 확인
nslookup ai.neuralgrid.kr

# 예상 결과:
# Name: ai.neuralgrid.kr
# Address: 115.91.5.140 (또는 Cloudflare 프록시 IP)
```

### 2단계: 배포 스크립트 실행

```bash
cd /home/azamans/webapp
./setup-ai-subdomain.sh
```

스크립트가 자동으로:
- ✅ Nginx 설정 업로드
- ✅ 사이트 활성화
- ✅ Nginx 테스트 및 리로드
- ✅ SSL 인증서 설정
- ✅ HTTPS 접속 확인

### 3단계: 서비스 확인

```bash
# HTTPS 접속 테스트
curl -I https://ai.neuralgrid.kr/

# 브라우저에서 접속
# https://ai.neuralgrid.kr
```

---

## 📱 Cloudflare 모바일 앱에서 설정하기

### iOS/Android 앱 사용 시

1. **Cloudflare 앱** 열기
2. **neuralgrid.kr** 도메인 선택
3. **DNS** 탭으로 이동
4. **+** 버튼 (레코드 추가)
5. 다음 정보 입력:
   - Type: **A**
   - Name: **ai**
   - IPv4: **115.91.5.140**
   - Proxy: **ON** (주황색)
6. **저장**

---

## 🔍 DNS 전파 확인 방법

### 방법 1: nslookup (추천)
```bash
nslookup ai.neuralgrid.kr
```

### 방법 2: dig
```bash
dig ai.neuralgrid.kr +short
```

### 방법 3: 온라인 도구
- https://dnschecker.org/#A/ai.neuralgrid.kr
- https://www.whatsmydns.net/#A/ai.neuralgrid.kr

### 방법 4: ping
```bash
ping ai.neuralgrid.kr
```

---

## ⏱️ 예상 소요 시간

| 단계 | 작업 | 소요 시간 |
|------|------|-----------|
| 1 | DNS 레코드 추가 (Cloudflare) | 2분 |
| 2 | DNS 전파 대기 | 1-5분 |
| 3 | 배포 스크립트 실행 | 5분 |
| 4 | 서비스 확인 및 테스트 | 3분 |
| **총계** | **전체 배포 프로세스** | **11-15분** |

---

## 📞 문제 발생 시

### DNS가 전파되지 않는 경우

1. **Cloudflare 설정 재확인**
   - 레코드가 올바르게 저장되었는지 확인
   - 프록시 상태가 ON(주황색)인지 확인

2. **DNS 캐시 클리어**
   ```bash
   # Linux
   sudo systemd-resolve --flush-caches
   
   # macOS
   sudo dscacheutil -flushcache
   
   # Windows
   ipconfig /flushdns
   ```

3. **다른 DNS 서버로 테스트**
   ```bash
   nslookup ai.neuralgrid.kr 8.8.8.8  # Google DNS
   nslookup ai.neuralgrid.kr 1.1.1.1  # Cloudflare DNS
   ```

4. **대기 시간 연장**
   - 일반적으로 1-5분이지만 최대 30분까지 걸릴 수 있음
   - 인내심을 가지고 대기

---

## ✅ 완료 체크리스트

- [ ] Cloudflare 대시보드 로그인
- [ ] neuralgrid.kr 도메인 선택
- [ ] DNS 레코드 추가 (Type: A, Name: ai, IPv4: 115.91.5.140)
- [ ] 프록시 상태 ON으로 설정 (주황색 구름)
- [ ] 저장 클릭
- [ ] DNS 전파 확인 (`nslookup ai.neuralgrid.kr`)
- [ ] 배포 스크립트 실행 (`./setup-ai-subdomain.sh`)
- [ ] HTTPS 접속 확인 (https://ai.neuralgrid.kr)
- [ ] 서비스 기능 테스트

---

## 🎯 빠른 참조

### DNS 레코드 (복사용)

```
Type: A
Name: ai
IPv4: 115.91.5.140
Proxy: ON
TTL: Auto
```

### 확인 명령어

```bash
# DNS 확인
nslookup ai.neuralgrid.kr

# HTTPS 테스트
curl -I https://ai.neuralgrid.kr/

# 배포 실행
cd /home/azamans/webapp && ./setup-ai-subdomain.sh
```

---

**문서 버전**: 1.0.0
**작성일**: 2025-12-15
**상태**: ✅ DNS 설정 대기 중
**다음 단계**: Cloudflare에 DNS 레코드 추가

🎯 **지금 해야 할 일**: 
1. Cloudflare에 `ai` 서브도메인 DNS 레코드 추가
2. 1-5분 대기
3. `./setup-ai-subdomain.sh` 실행
