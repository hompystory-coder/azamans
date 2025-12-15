# 🌐 다중 서버 대시보드 사용 가이드

## ✅ 완료된 작업

다중 서버를 중앙에서 모니터링할 수 있는 통합 대시보드가 구축되었습니다!

---

## 📊 새로운 대시보드 화면

### 메인 대시보드: https://ddos.neuralgrid.kr/

```
┌─────────────────────────────────────────────────────────┐
│      🛡️ NeuralGrid DDoS Defense                         │
│      Multi-Server Real-time Monitoring Dashboard        │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                     📊 전체 통계                         │
├─────────────────────────────────────────────────────────┤
│  총 서버 수      온라인 서버    전체 트래픽   차단된 IP  │
│      3              3            245 req/s      7개      │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                   🖥️ 서버 현황                           │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────────┐  ┌──────────────────┐            │
│  │ 서버 A [LOCAL]    │  │ 서버 B [REMOTE]  │            │
│  │ 115.91.5.140     │  │ 192.168.1.10     │            │
│  │ 🟢 온라인         │  │ 🟢 온라인         │            │
│  │                  │  │                  │            │
│  │ 트래픽: 120 req/s│  │ 트래픽: 85 req/s │            │
│  │ 정상/차단: 67/15 │  │ 정상/차단: 80/5  │            │
│  │ 차단 IP: 2개     │  │ 차단 IP: 3개     │            │
│  │ CPU: 0.53        │  │ CPU: 0.82        │            │
│  │ 메모리: 17.4%    │  │ 메모리: 45.2%    │            │
│  │                  │  │                  │            │
│  │ [상세보기]        │  │ [상세보기] [제거] │            │
│  └──────────────────┘  └──────────────────┘            │
│                                                          │
│  ┌──────────────────┐  ┌──────────────────┐            │
│  │ 서버 C [REMOTE]  │  │ 서버 D [REMOTE]  │            │
│  │ 192.168.1.11     │  │ 192.168.1.12     │            │
│  │ 🟢 온라인         │  │ 🔴 오프라인       │            │
│  │                  │  │                  │            │
│  │ 트래픽: 40 req/s │  │ 트래픽: -        │            │
│  │ 정상/차단: 38/2  │  │ 정상/차단: -     │            │
│  │ 차단 IP: 2개     │  │ 차단 IP: -       │            │
│  │ CPU: 0.25        │  │ CPU: -           │            │
│  │ 메모리: 28.5%    │  │ 메모리: -        │            │
│  │                  │  │                  │            │
│  │ [상세보기] [제거] │  │ [상세보기] [제거] │            │
│  └──────────────────┘  └──────────────────┘            │
└─────────────────────────────────────────────────────────┘
```

---

## 🚀 사용 방법

### 1️⃣ 메인 대시보드 접속

```
https://ddos.neuralgrid.kr/
```

**기능:**
- ✅ 모든 서버를 한 화면에서 모니터링
- ✅ 실시간 업데이트 (1초마다 자동 갱신)
- ✅ 서버 상태 (온라인/오프라인) 자동 감지
- ✅ 전체 통계 (총 서버, 트래픽, 차단 IP)
- ✅ 서버별 상세 정보 (트래픽, CPU, 메모리)

### 2️⃣ 다른 서버 추가하기

#### A. Agent 설치 (자동)
```bash
# 다른 서버에 SSH 접속
ssh user@your-server.com

# Agent 자동 설치
curl -fsSL https://raw.githubusercontent.com/hompystory-coder/azamans/main/install-ddos-agent.sh | sudo bash
```

#### B. Agent 설치 (수동)
```bash
# 1. Agent 다운로드
sudo curl -o /usr/local/bin/ddos-agent.sh \
    https://raw.githubusercontent.com/hompystory-coder/azamans/main/ddos-agent.sh

# 2. 실행 권한
sudo chmod +x /usr/local/bin/ddos-agent.sh

# 3. 설정 수정
sudo nano /usr/local/bin/ddos-agent.sh
# CENTRAL_SERVER="https://ddos.neuralgrid.kr" 확인

# 4. 서비스 등록
sudo systemctl enable ddos-agent
sudo systemctl start ddos-agent
sudo systemctl status ddos-agent
```

#### C. 대시보드에서 확인
```
https://ddos.neuralgrid.kr/
→ 새 서버가 자동으로 나타남!
```

### 3️⃣ 단일 서버 상세보기

로컬 서버의 상세 정보를 보려면:
```
https://ddos.neuralgrid.kr/dashboard.html
```

**기능:**
- ✅ 실시간 트래픽 그래프
- ✅ 차단된 IP 목록
- ✅ Fail2ban 상태
- ✅ 실시간 로그

---

## 📡 API 엔드포인트

### 서버 등록
```bash
curl -X POST https://ddos.neuralgrid.kr/api/server/register \
  -H "Content-Type: application/json" \
  -d '{
    "serverId": "server-web01",
    "serverName": "Web Server 01",
    "serverIp": "192.168.1.10"
  }'
```

### 통계 전송
```bash
curl -X POST https://ddos.neuralgrid.kr/api/server/server-web01/stats \
  -H "Content-Type: application/json" \
  -d '{
    "traffic": {
      "totalRequests": 1000,
      "requestsPerSecond": 50,
      "normalTraffic": 45,
      "blockedTraffic": 5
    },
    "blockedIPs": {
      "count": 3
    },
    "systemStatus": {
      "load": 0.5,
      "memory": 35.2,
      "uptime": "5 days",
      "status": "normal"
    }
  }'
```

### 전체 서버 목록 조회
```bash
curl https://ddos.neuralgrid.kr/api/servers | jq '.'
```

### 특정 서버 정보 조회
```bash
curl https://ddos.neuralgrid.kr/api/server/server-web01 | jq '.'
```

### 서버 제거
```bash
curl -X DELETE https://ddos.neuralgrid.kr/api/server/server-web01
```

---

## 🎯 실제 사용 시나리오

### 시나리오 1: 웹 서버 3대 모니터링

```bash
# 서버 1 (웹 서버)
ssh root@web1.example.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 서버 2 (API 서버)
ssh root@api.example.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 서버 3 (데이터베이스 서버)
ssh root@db.example.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 대시보드에서 확인
# → https://ddos.neuralgrid.kr/
# → 3대 서버 모두 실시간 모니터링!
```

### 시나리오 2: 고객사 서버 모니터링

```bash
# 고객사 A
ssh admin@customer-a.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 고객사 B
ssh admin@customer-b.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 고객사 C
ssh admin@customer-c.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 중앙 관리자는 https://ddos.neuralgrid.kr/ 에서
# 모든 고객사 서버를 한눈에 모니터링!
```

---

## 🔍 주요 기능

### 1. 실시간 모니터링
- ⏱️ **1초마다 자동 업데이트**
- 📊 **트래픽 통계** (req/s, 정상/차단)
- 🚫 **차단된 IP 수**
- 💻 **시스템 상태** (CPU, 메모리)

### 2. 서버 상태 자동 감지
- 🟢 **온라인**: 최근 1분 이내 데이터 수신
- 🔴 **오프라인**: 1분 이상 응답 없음
- 🏷️ **LOCAL/REMOTE** 배지로 구분

### 3. 전체 통계
- 📈 **총 서버 수**
- ✅ **온라인 서버 수**
- 🌐 **전체 트래픽 합계**
- 🚫 **전체 차단 IP 합계**

### 4. 서버 관리
- ➕ **자동 등록** (Agent 설치 시)
- 🗑️ **서버 제거** (대시보드에서)
- 📊 **상세보기** (서버별 상세 정보)

---

## 📱 반응형 디자인

대시보드는 모든 화면 크기에 최적화되어 있습니다:

- 🖥️ **데스크톱**: 4열 그리드
- 💻 **노트북**: 3열 그리드
- 📱 **태블릿**: 2열 그리드
- 📱 **모바일**: 1열 스택

---

## 🎨 UI 특징

### 서버 카드 색상
- 🟢 **온라인**: 초록색 테두리
- 🔴 **오프라인**: 빨간색 테두리, 투명도 감소
- 💜 **로컬 서버**: 보라색 그라디언트 상단 바

### 상태 표시
- **정상**: 초록색
- **경고**: 주황색 (CPU > 1.0, 메모리 > 60%)
- **위험**: 빨간색 (CPU > 2.0, 메모리 > 80%)

---

## 🔧 문제 해결

### 서버가 나타나지 않을 때

1. **Agent 상태 확인**
```bash
sudo systemctl status ddos-agent
```

2. **로그 확인**
```bash
sudo tail -f /var/log/ddos-agent.log
```

3. **중앙 서버 연결 확인**
```bash
curl https://ddos.neuralgrid.kr/api/status
```

### 서버가 오프라인으로 표시될 때

- Agent가 1분 이상 응답하지 않으면 자동으로 오프라인 표시
- Agent 재시작: `sudo systemctl restart ddos-agent`

### 데이터가 업데이트되지 않을 때

- 브라우저 새로고침: `Ctrl + F5` (캐시 무시)
- 브라우저 콘솔 확인: `F12` → Console 탭

---

## 📊 성능

- **서버 수 제한**: 무제한
- **업데이트 주기**: 1초 (대시보드), 30초 (Agent)
- **서버 부하**: 매우 낮음 (Agent는 경량 스크립트)
- **네트워크 대역폭**: 최소 (JSON 데이터만 전송)

---

## 💼 비즈니스 활용

### SaaS 서비스로 판매

```
무료 플랜: 1개 서버
베이직: $29/월 - 5개 서버
프로: $99/월 - 20개 서버
엔터프라이즈: 맞춤 가격 - 무제한 서버
```

### 고객사 서비스

- 고객사 서버에 Agent 설치
- 중앙에서 모든 고객사 모니터링
- 월간/연간 보고서 자동 생성
- SLA 관리 및 알림

---

## 🔗 관련 링크

- **메인 대시보드**: https://ddos.neuralgrid.kr/
- **단일 서버 뷰**: https://ddos.neuralgrid.kr/dashboard.html
- **API 문서**: https://ddos.neuralgrid.kr/api/servers
- **GitHub**: https://github.com/hompystory-coder/azamans
- **설치 가이드**: [MULTI_SERVER_DEPLOYMENT.md](./MULTI_SERVER_DEPLOYMENT.md)
- **빠른 시작**: [QUICK_START_GUIDE.md](./QUICK_START_GUIDE.md)

---

## ✅ 체크리스트

다음 단계로 다중 서버 모니터링을 시작하세요:

- [x] ✅ 중앙 서버 구축 완료 (ddos.neuralgrid.kr)
- [x] ✅ 다중 서버 대시보드 구축 완료
- [x] ✅ Multi-Server API 구현 완료
- [x] ✅ Agent 스크립트 작성 완료
- [x] ✅ 자동 설치 스크립트 작성 완료
- [ ] 🔲 다른 서버에 Agent 설치
- [ ] 🔲 대시보드에서 여러 서버 확인
- [ ] 🔲 실시간 모니터링 시작!

---

**이제 여러 서버를 중앙에서 모니터링할 수 있습니다!** 🎉

**대시보드**: https://ddos.neuralgrid.kr/
