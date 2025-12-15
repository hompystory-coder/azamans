# 🛡️ NeuralGrid DDoS 방어 시스템 설계

## 📋 개요

**프로젝트명**: NeuralGrid DDoS Defense System
**목표**: 다층 DDoS 방어 시스템 구축
**서브도메인**: `defense.neuralgrid.kr` 또는 `shield.neuralgrid.kr`
**서버**: 115.91.5.140

---

## 🏗️ 시스템 아키텍처

### 다층 방어 구조

```
┌─────────────────────────────────────────────────────────────┐
│                    계층 1: Cloudflare                        │
│                  (DDoS 보호, WAF, Rate Limiting)              │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│                    계층 2: Nginx                             │
│          (Rate Limiting, Connection Limiting, GeoIP)          │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│                    계층 3: Fail2ban                          │
│              (자동 IP 차단, 패턴 감지, 로그 분석)             │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│                    계층 4: UFW/iptables                      │
│                  (방화벽, IP 화이트리스트)                     │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              계층 5: 실시간 모니터링 대시보드                  │
│         (트래픽 분석, 공격 탐지, 알림, 자동 대응)              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 구현할 기능

### 1. Nginx Rate Limiting ⭐
**우선순위**: 높음

**기능:**
- 초당 요청 수 제한 (Rate Limiting)
- 동시 연결 수 제한 (Connection Limiting)
- Burst 제어
- IP 기반 제한

**설정:**
```nginx
# Rate Limiting Zone 정의
limit_req_zone $binary_remote_addr zone=general:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=api:10m rate=30r/s;
limit_conn_zone $binary_remote_addr zone=conn_limit:10m;

# 적용
limit_req zone=general burst=20 nodelay;
limit_conn conn_limit 10;
```

**효과:**
- ✅ HTTP Flood 방어
- ✅ Slowloris 공격 방어
- ✅ API 남용 방지

---

### 2. Fail2ban 자동 차단 ⭐
**우선순위**: 높음

**기능:**
- 실패한 로그인 시도 감지
- 비정상적인 요청 패턴 감지
- 자동 IP 차단 (iptables)
- 일정 시간 후 자동 해제

**필터 패턴:**
```
# 404 에러 반복
- 10초 내 404 에러 10회 → 1시간 차단

# 로그인 실패
- 5분 내 로그인 실패 5회 → 30분 차단

# 비정상 User-Agent
- Bot, Scanner 감지 → 즉시 차단
```

**효과:**
- ✅ 브루트포스 공격 방어
- ✅ 스캔 시도 차단
- ✅ 악성 봇 차단

---

### 3. 실시간 모니터링 대시보드 ⭐
**우선순위**: 높음

**기능:**
- 실시간 트래픽 그래프
- 차단된 IP 목록
- 공격 유형 분석
- 지리적 위치 표시
- 알림 로그

**기술 스택:**
- Frontend: HTML5 + Chart.js
- Backend: Node.js + Express
- 데이터: Nginx 로그 + Fail2ban 로그
- 실시간: WebSocket

**URL:**
- `https://defense.neuralgrid.kr` 또는 `https://shield.neuralgrid.kr`

---

### 4. IP 화이트리스트/블랙리스트 ⭐
**우선순위**: 높음

**화이트리스트:**
- 관리자 IP
- 신뢰할 수 있는 서비스 IP
- API 파트너 IP

**블랙리스트:**
- 알려진 공격 IP
- Tor Exit Node
- VPN/Proxy IP (선택적)

**관리:**
- 웹 대시보드에서 추가/삭제
- 자동 동기화 (Nginx + UFW)

---

### 5. 자동 대응 시스템
**우선순위**: 중간

**트리거:**
- 초당 요청 1000개 초과 → Emergency Mode
- CPU 사용률 90% 초과 → Rate Limit 강화
- 특정 IP에서 초당 50개 요청 → 자동 차단

**대응:**
- 자동 Rate Limit 조정
- 자동 IP 차단
- 관리자 알림 (Slack/Email)
- 로그 기록

---

### 6. 알림 시스템
**우선순위**: 중간

**알림 채널:**
- Email (admin@neuralgrid.kr)
- Slack (선택)
- Telegram (선택)

**알림 이벤트:**
- 대규모 공격 감지
- 서버 리소스 임계치 초과
- 중요 IP 차단
- 시스템 장애

---

## 📊 대시보드 기능

### 메인 화면
```
┌─────────────────────────────────────────────────────────────┐
│  🛡️ NeuralGrid DDoS Defense Dashboard                       │
├─────────────────────────────────────────────────────────────┤
│  📊 실시간 통계                                              │
│  ├─ 초당 요청: 245 req/s                                     │
│  ├─ 차단된 IP: 1,234개                                      │
│  ├─ 활성 공격: 0건                                          │
│  └─ 시스템 상태: 🟢 정상                                     │
├─────────────────────────────────────────────────────────────┤
│  📈 트래픽 그래프 (Chart.js)                                 │
│  [실시간 라인 차트: 정상/차단/의심 트래픽]                    │
├─────────────────────────────────────────────────────────────┤
│  🚫 최근 차단된 IP                                           │
│  ├─ 192.168.1.100 (중국) - HTTP Flood                       │
│  ├─ 203.0.113.50 (러시아) - Brute Force                     │
│  └─ 198.51.100.25 (미국) - Scanner                          │
├─────────────────────────────────────────────────────────────┤
│  🌍 공격 지리적 분포                                         │
│  [세계 지도 히트맵]                                          │
├─────────────────────────────────────────────────────────────┤
│  ⚙️ 빠른 작업                                               │
│  [IP 차단] [화이트리스트 추가] [긴급 모드] [설정]            │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 기술 스택

### Backend
- **Node.js + Express**: API 서버
- **Python**: 로그 분석 스크립트
- **Bash**: 자동화 스크립트

### Frontend
- **HTML5 + CSS3**: 대시보드 UI
- **Chart.js**: 실시간 그래프
- **WebSocket**: 실시간 업데이트

### 보안 도구
- **Nginx**: Rate Limiting, Connection Limiting
- **Fail2ban**: 자동 IP 차단
- **UFW/iptables**: 방화벽
- **GeoIP**: 지리적 위치 확인

### 모니터링
- **Prometheus** (선택): 메트릭 수집
- **Grafana** (선택): 고급 시각화

---

## 📦 구현 단계

### Phase 1: 기본 방어 (1시간)
1. ✅ Nginx Rate Limiting 설정
2. ✅ Fail2ban 설치 및 설정
3. ✅ UFW 방화벽 규칙 강화
4. ✅ 기본 IP 블랙리스트 적용

### Phase 2: 모니터링 (1.5시간)
5. ✅ 실시간 대시보드 개발
6. ✅ 로그 파싱 시스템
7. ✅ 트래픽 분석 API
8. ✅ 차단 IP 목록 API

### Phase 3: 자동화 (1시간)
9. ✅ 자동 대응 시스템
10. ✅ 알림 시스템 (Email)
11. ✅ 화이트리스트/블랙리스트 관리
12. ✅ 긴급 모드 구현

### Phase 4: 배포 (30분)
13. ✅ 서브도메인 설정 (defense.neuralgrid.kr)
14. ✅ SSL 인증서 설정
15. ✅ 통합 테스트
16. ✅ 문서화

**총 예상 시간: 4시간**

---

## 🎯 성능 목표

### Rate Limiting
- 일반 페이지: 10 req/s per IP
- API 엔드포인트: 30 req/s per IP
- 동시 연결: 10 connections per IP

### 자동 차단
- 404 에러: 10회/10초 → 1시간 차단
- 로그인 실패: 5회/5분 → 30분 차단
- HTTP Flood: 100회/1초 → 24시간 차단

### 응답 시간
- 대시보드 로딩: <2초
- API 응답: <100ms
- 실시간 업데이트: <1초

---

## 🔒 보안 고려사항

### 대시보드 접근
- JWT 기반 인증 (auth.neuralgrid.kr 통합)
- IP 화이트리스트 (관리자 IP만)
- 2FA (선택적)

### 데이터 보호
- 로그 암호화
- IP 주소 마스킹 (GDPR 준수)
- 정기적 로그 삭제 (30일)

### 시스템 보안
- 대시보드 Rate Limiting
- SQL Injection 방어
- XSS 방어
- CSRF 토큰

---

## 📊 예상 효과

### 공격 방어율
- HTTP Flood: 99.9%
- Slowloris: 99%
- Brute Force: 100%
- Scanner/Bot: 95%

### 리소스 절약
- CPU 사용률: 30% 감소
- 대역폭: 50% 절약
- 서버 부하: 40% 감소

### 가용성 향상
- Uptime: 99.9% → 99.99%
- 응답 시간: 안정화
- 공격 시 서비스 유지

---

## 🚀 빠른 시작 가이드

### 긴급 상황 대응

**공격 감지 시:**
```bash
# 1. 대시보드 확인
https://defense.neuralgrid.kr

# 2. 수동 IP 차단
sudo fail2ban-client set nginx-limit-req banip <IP>

# 3. 긴급 모드 활성화 (Rate Limit 강화)
sudo systemctl reload nginx-emergency

# 4. 로그 확인
sudo tail -f /var/log/nginx/access.log
sudo fail2ban-client status nginx-limit-req
```

**공격 종료 후:**
```bash
# 1. IP 차단 해제 (필요시)
sudo fail2ban-client set nginx-limit-req unbanip <IP>

# 2. 정상 모드 복구
sudo systemctl reload nginx

# 3. 로그 분석
sudo /opt/ddos-defense/analyze-attack.sh
```

---

## 📝 다음 단계

1. **즉시 작업**:
   - Nginx Rate Limiting 설정 적용
   - Fail2ban 설치 및 설정
   - 기본 방화벽 규칙 적용

2. **1차 목표** (2시간):
   - 실시간 대시보드 개발
   - 자동 차단 시스템 구축

3. **2차 목표** (2시간):
   - 알림 시스템 통합
   - 고급 분석 기능

4. **최종 목표**:
   - defense.neuralgrid.kr 배포
   - 전체 시스템 테스트
   - 운영 가이드 작성

---

**문서 버전**: 1.0.0
**작성일**: 2025-12-15
**상태**: 📋 설계 완료, 구현 준비
**다음 단계**: Nginx Rate Limiting 설정
