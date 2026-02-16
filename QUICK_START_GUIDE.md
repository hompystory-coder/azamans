# 🚀 DDoS 방어 시스템 - 빠른 시작 가이드

## 📌 목표
다른 서버들에서 DDoS 방어 시스템을 사용하여 `ddos.neuralgrid.kr` 대시보드에서 모니터링하기

---

## 🎯 1단계: 중앙 서버 확인

현재 운영 중인 중앙 서버:
- **URL**: https://ddos.neuralgrid.kr/
- **상태**: 🟢 정상 운영 중
- **서버**: 115.91.5.140:3105

---

## 🎯 2단계: 다른 서버에 Agent 설치

### 방법 A: 자동 설치 (추천)

```bash
# 1. 설치 스크립트 다운로드 및 실행
curl -fsSL https://ddos.neuralgrid.kr/install.sh | sudo bash

# 또는 GitHub에서 직접 다운로드
curl -fsSL https://raw.githubusercontent.com/hompystory-coder/azamans/main/install-ddos-agent.sh | sudo bash
```

### 방법 B: 수동 설치

```bash
# 1. Agent 스크립트 다운로드
sudo curl -o /usr/local/bin/ddos-agent.sh \
    https://raw.githubusercontent.com/hompystory-coder/azamans/main/ddos-agent.sh

# 2. 실행 권한 부여
sudo chmod +x /usr/local/bin/ddos-agent.sh

# 3. 설정 수정 (중앙 서버 URL과 API Key)
sudo nano /usr/local/bin/ddos-agent.sh
# CENTRAL_SERVER="https://ddos.neuralgrid.kr"
# API_KEY="your-api-key-here"

# 4. Systemd 서비스 생성
sudo tee /etc/systemd/system/ddos-agent.service > /dev/null << 'EOF'
[Unit]
Description=DDoS Defense Agent
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/bin/ddos-agent.sh
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOF

# 5. 서비스 시작
sudo systemctl daemon-reload
sudo systemctl enable ddos-agent
sudo systemctl start ddos-agent

# 6. 상태 확인
sudo systemctl status ddos-agent
```

---

## 🎯 3단계: 작동 확인

### A. 서버에서 로그 확인
```bash
# 실시간 로그 보기
sudo tail -f /var/log/ddos-agent.log

# Systemd 로그 확인
sudo journalctl -u ddos-agent -f
```

### B. 대시보드에서 확인
1. 브라우저에서 https://ddos.neuralgrid.kr/ 접속
2. **서버 목록**에서 새로 추가된 서버 확인
3. 실시간 트래픽 및 통계 확인

---

## 🎯 4단계: 여러 서버 관리

### 서버 추가 (각 서버에서 실행)
```bash
# 서버 A
ssh user@server-a.example.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | sudo bash

# 서버 B
ssh user@server-b.example.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | sudo bash

# 서버 C
ssh user@server-c.example.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | sudo bash
```

### 대시보드에서 확인
```
https://ddos.neuralgrid.kr/
┌─────────────────────────────────────────────┐
│  🖥️ 서버 현황                                │
├─────────────────────────────────────────────┤
│  [서버 A]  [서버 B]  [서버 C]                │
│  🟢 온라인  🟢 온라인  🟢 온라인              │
│  120 req/s  85 req/s   95 req/s             │
│  2개 차단   5개 차단   1개 차단              │
└─────────────────────────────────────────────┘
```

---

## 📊 대시보드 기능

### 실시간 모니터링
- ✅ 서버별 트래픽 통계
- ✅ 차단된 IP 목록
- ✅ 시스템 부하 (CPU, 메모리)
- ✅ 실시간 그래프

### 통합 관리
- ✅ 모든 서버를 한 화면에서 확인
- ✅ 서버 온/오프라인 상태
- ✅ 공격 감지 및 알림
- ✅ 차단 IP 통합 관리

---

## 🔧 문제 해결

### Agent가 시작되지 않을 때
```bash
# 로그 확인
sudo journalctl -u ddos-agent -n 50

# 수동 실행으로 오류 확인
sudo /usr/local/bin/ddos-agent.sh
```

### 중앙 서버 연결 안 될 때
```bash
# 네트워크 확인
curl -I https://ddos.neuralgrid.kr/

# 방화벽 확인
sudo ufw status

# DNS 확인
nslookup ddos.neuralgrid.kr
```

### Fail2ban 권한 오류
```bash
# Sudoers 파일 확인
sudo visudo -f /etc/sudoers.d/ddos-agent

# 다음 내용 추가
root ALL=(ALL) NOPASSWD: /usr/bin/fail2ban-client
```

---

## 💡 사용 예시

### 예시 1: 웹 서버 3대 모니터링
```bash
# 웹 서버 1 (192.168.1.10)
ssh root@192.168.1.10
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 웹 서버 2 (192.168.1.11)
ssh root@192.168.1.11
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 웹 서버 3 (192.168.1.12)
ssh root@192.168.1.12
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 대시보드에서 3대 서버 모두 확인 가능
```

### 예시 2: 고객사 서버 모니터링
```bash
# 고객사 A의 서버
ssh admin@customer-a.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 고객사 B의 서버
ssh admin@customer-b.com
curl -fsSL https://ddos.neuralgrid.kr/install.sh | bash

# 중앙에서 모든 고객사 서버 모니터링
```

---

## 📦 배포 파일 목록

현재 GitHub에 업로드된 파일:
- ✅ `ddos-agent.sh` - Agent 스크립트
- ✅ `install-ddos-agent.sh` - 자동 설치 스크립트
- ✅ `MULTI_SERVER_DEPLOYMENT.md` - 상세 배포 가이드
- ✅ `QUICK_START_GUIDE.md` - 이 문서

---

## 🔗 유용한 링크

- **대시보드**: https://ddos.neuralgrid.kr/
- **GitHub**: https://github.com/hompystory-coder/azamans
- **문서**: https://github.com/hompystory-coder/azamans/blob/main/MULTI_SERVER_DEPLOYMENT.md

---

## 📞 지원

문제가 발생하면:
1. 로그 확인: `sudo journalctl -u ddos-agent -n 100`
2. 서비스 재시작: `sudo systemctl restart ddos-agent`
3. 대시보드 확인: https://ddos.neuralgrid.kr/

---

**이제 여러 서버를 중앙에서 모니터링할 수 있습니다!** 🎉
