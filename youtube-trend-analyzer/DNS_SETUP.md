# 🌐 neuralgrid.kr DNS 설정 가이드

YouTube Trend Analyzer를 `youtube-trend.neuralgrid.kr` 서브도메인으로 접속하기 위한 DNS 설정 가이드입니다.

## 📋 필요한 DNS 레코드

### 옵션 1: A 레코드 (권장)

서버 IP 주소를 직접 지정하는 방법입니다.

```
Type: A
Name: youtube-trend
Value: [서버 IP 주소]
TTL: 3600 (1시간)
```

**예시:**
- 서버 IP가 `123.456.789.012`인 경우:
```
Type: A
Name: youtube-trend
Value: 123.456.789.012
TTL: 3600
```

### 옵션 2: CNAME 레코드

메인 도메인을 가리키는 방법입니다.

```
Type: CNAME
Name: youtube-trend
Value: neuralgrid.kr
TTL: 3600
```

---

## 🔧 DNS 설정 방법

### 1️⃣ 도메인 등록 업체 로그인

neuralgrid.kr 도메인을 관리하는 업체에 로그인합니다.
- 가비아 (Gabia)
- 카페24
- 후이즈
- AWS Route 53
- Cloudflare
- 기타 도메인 등록 업체

### 2️⃣ DNS 관리 페이지 접속

각 업체별 DNS 관리 페이지:
- **가비아**: My가비아 > 도메인 > DNS 정보
- **카페24**: 도메인 관리 > DNS 설정
- **후이즈**: 도메인 관리 > DNS 설정
- **AWS Route 53**: Hosted zones > 레코드 관리
- **Cloudflare**: DNS > Records

### 3️⃣ 새 DNS 레코드 추가

#### A 레코드 추가 (권장)

1. "레코드 추가" 또는 "Add Record" 클릭
2. 다음 정보 입력:
   - **Type (타입)**: A
   - **Name (호스트)**: youtube-trend
   - **Value (값/IP)**: 서버 IP 주소 입력
   - **TTL**: 3600 (또는 1h)
3. 저장

#### CNAME 레코드 추가 (대안)

1. "레코드 추가" 또는 "Add Record" 클릭
2. 다음 정보 입력:
   - **Type (타입)**: CNAME
   - **Name (호스트)**: youtube-trend
   - **Value (값)**: neuralgrid.kr
   - **TTL**: 3600 (또는 1h)
3. 저장

---

## ✅ DNS 설정 확인

### 1. 명령줄에서 확인

```bash
# Linux/Mac
nslookup youtube-trend.neuralgrid.kr

# 또는
dig youtube-trend.neuralgrid.kr

# 또는
host youtube-trend.neuralgrid.kr
```

**정상 응답 예시:**
```
Server:		8.8.8.8
Address:	8.8.8.8#53

Name:	youtube-trend.neuralgrid.kr
Address: 123.456.789.012
```

### 2. 온라인 도구로 확인

다음 웹사이트에서 확인 가능:
- https://www.whatsmydns.net/
- https://dnschecker.org/
- https://mxtoolbox.com/

**검색어**: `youtube-trend.neuralgrid.kr`

---

## ⏰ DNS 전파 시간

DNS 레코드 추가 후 전파까지 시간이 걸립니다:

| 상황 | 예상 시간 |
|------|----------|
| 최소 | 5~10분 |
| 평균 | 30분~1시간 |
| 최대 | 24~48시간 |

**팁**: TTL 값을 낮추면 (예: 300초) 전파가 빠르지만, 트래픽이 많으면 DNS 조회 부하가 증가할 수 있습니다.

---

## 🔍 서버 IP 주소 확인 방법

현재 서버의 IP 주소를 모르는 경우:

```bash
# 공인 IP 확인
curl ifconfig.me

# 또는
curl icanhazip.com

# 또는
hostname -I
```

---

## 🌐 업체별 상세 설정 가이드

### 가비아 (Gabia)

1. [My가비아](https://my.gabia.com) 로그인
2. 서비스 관리 > 도메인
3. `neuralgrid.kr` 선택
4. DNS 정보 > DNS 관리 도구
5. 레코드 추가:
   ```
   타입: A
   호스트: youtube-trend
   값/위치: [서버 IP]
   TTL: 3600
   ```
6. 확인 클릭

### 카페24

1. [카페24 호스팅 센터](https://www.cafe24.com) 로그인
2. 나의 서비스 관리 > 도메인 관리
3. `neuralgrid.kr` > DNS 설정
4. 레코드 추가:
   ```
   타입: A
   호스트: youtube-trend
   값: [서버 IP]
   TTL: 3600
   ```
5. 추가 클릭

### AWS Route 53

1. [AWS Console](https://console.aws.amazon.com/route53/) 접속
2. Hosted zones 선택
3. `neuralgrid.kr` 호스팅 영역 선택
4. "Create record" 클릭
5. 레코드 정보 입력:
   ```
   Record name: youtube-trend
   Record type: A
   Value: [서버 IP]
   TTL: 3600
   ```
6. "Create records" 클릭

### Cloudflare

1. [Cloudflare Dashboard](https://dash.cloudflare.com) 로그인
2. `neuralgrid.kr` 도메인 선택
3. DNS > Records
4. "Add record" 클릭
5. 레코드 정보 입력:
   ```
   Type: A
   Name: youtube-trend
   IPv4 address: [서버 IP]
   TTL: Auto
   Proxy status: DNS only (회색 구름)
   ```
6. "Save" 클릭

---

## 🔐 SSL 인증서 발급 전 확인사항

SSL 인증서(HTTPS)를 발급하기 전에 **반드시**:

1. ✅ DNS 레코드가 올바르게 설정되어 있어야 함
2. ✅ DNS가 전파 완료되어야 함 (nslookup으로 확인)
3. ✅ 서버에서 80번 포트가 열려있어야 함
4. ✅ Nginx가 정상 작동해야 함

확인 후 SSL 설정:
```bash
sudo bash setup-ssl.sh
```

---

## ❌ 문제 해결

### DNS가 전파되지 않는 경우

**증상**: nslookup에서 결과가 나오지 않음

**해결책**:
1. DNS 레코드 설정 재확인
2. TTL 시간만큼 대기 (최소 5분)
3. DNS 캐시 초기화:
   ```bash
   # Linux
   sudo systemd-resolve --flush-caches
   
   # Mac
   sudo dscacheutil -flushcache
   
   # Windows
   ipconfig /flushdns
   ```
4. 다른 DNS 서버로 조회 시도:
   ```bash
   nslookup youtube-trend.neuralgrid.kr 8.8.8.8
   ```

### "NXDOMAIN" 오류

**증상**: DNS 조회 시 NXDOMAIN 오류

**해결책**:
1. 호스트 이름 확인 (`youtube-trend`)
2. 도메인 이름 확인 (`neuralgrid.kr`)
3. 레코드 타입 확인 (A 또는 CNAME)
4. 네임서버가 올바른지 확인

### 잘못된 IP 주소로 연결

**증상**: 다른 서버로 연결됨

**해결책**:
1. DNS 레코드의 IP 주소 확인
2. 이전 레코드 삭제 후 재생성
3. DNS 캐시 초기화

---

## 📞 도움말

### 추가 지원이 필요한 경우:

1. **도메인 업체 고객센터** 문의
2. **서버 관리자** 문의
3. DNS 설정 관련 문서 참조

### 유용한 명령어

```bash
# DNS 레코드 확인
nslookup youtube-trend.neuralgrid.kr

# 상세 DNS 정보 확인
dig youtube-trend.neuralgrid.kr

# 네임서버 확인
nslookup -type=ns neuralgrid.kr

# 모든 레코드 확인
dig youtube-trend.neuralgrid.kr ANY
```

---

## ✅ 완료 체크리스트

설정 완료 전 확인:

- [ ] 도메인 등록 업체 로그인
- [ ] DNS 관리 페이지 접속
- [ ] A 또는 CNAME 레코드 추가
- [ ] 레코드 정보 저장
- [ ] DNS 전파 대기 (5~30분)
- [ ] nslookup으로 확인
- [ ] 웹 브라우저에서 접속 테스트
- [ ] SSL 인증서 발급 (선택사항)

---

**설정 완료 후 접속**: http://youtube-trend.neuralgrid.kr
**SSL 설정 후**: https://youtube-trend.neuralgrid.kr

🎉 DNS 설정 완료!
