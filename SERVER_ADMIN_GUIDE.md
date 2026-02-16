# 📋 서버 담당자용 - DDoS 모니터링 Agent 설치 가이드

> **소요 시간**: 1-2분  
> **난이도**: ⭐ (매우 쉬움)  
> **필요 권한**: sudo 또는 root

---

## 🎯 설치 명령어 (이것만 하면 됩니다!)

```bash
curl -fsSL https://raw.githubusercontent.com/hompystory-coder/azamans/main/install-ddos-agent.sh | sudo bash
```

### 📝 설치 절차

1. **서버에 SSH 접속**
   ```bash
   ssh your-username@your-server-ip
   ```

2. **위 명령어 복사 후 붙여넣기 + Enter**

3. **비밀번호 입력** (sudo 권한 필요 시)

4. **"설치가 완료되었습니다" 메시지 확인**

5. **대시보드에서 확인**: https://ddos.neuralgrid.kr/

---

## ✅ 설치 완료 확인

### 서비스 상태 확인
```bash
sudo systemctl status ddos-agent
```

**정상 작동 시 출력**:
```
● ddos-agent.service - DDoS Defense Agent
   Active: active (running) since ...
```

### 로그 확인
```bash
sudo tail -f /var/log/ddos-agent.log
```

**정상 작동 시 출력**:
```
[2025-12-15 15:30:00] 서버 등록 시작...
[2025-12-15 15:30:01] ✅ 서버 등록 완료
[2025-12-15 15:30:02] 데이터 수집 중...
[2025-12-15 15:30:03] ✅ 데이터 전송 성공
```

---

## 🔧 문제 해결

### ❌ "Permission denied" 오류
**해결**: sudo를 붙여서 실행
```bash
sudo bash -c "curl -fsSL https://raw.githubusercontent.com/hompystory-coder/azamans/main/install-ddos-agent.sh | bash"
```

### ❌ "curl: command not found" 오류
**해결**: curl 설치 후 재시도
```bash
# Ubuntu/Debian
sudo apt-get update && sudo apt-get install curl -y

# CentOS/RHEL
sudo yum install curl -y
```

### ❌ 대시보드에 서버가 안 나타남
**확인 사항**:
1. Agent가 실행 중인지 확인: `sudo systemctl status ddos-agent`
2. 로그 확인: `sudo tail -f /var/log/ddos-agent.log`
3. 중앙 서버 연결 확인: `curl https://ddos.neuralgrid.kr/api/status`

**해결**: 서비스 재시작
```bash
sudo systemctl restart ddos-agent
```

---

## 📊 모니터링되는 정보

Agent가 수집하는 정보:
- ✅ **트래픽 통계**: 요청 수, 초당 요청 수
- ✅ **차단 통계**: 차단된 트래픽, 차단된 IP 개수
- ✅ **시스템 상태**: CPU 부하, 메모리 사용률
- ✅ **서버 정보**: 서버 IP, 호스트명

**수집하지 않는 정보**:
- ❌ 개인정보
- ❌ 파일 내용
- ❌ 데이터베이스 정보
- ❌ 비밀번호

---

## 🗑️ 제거 방법

Agent를 제거하고 싶을 때:

```bash
# 1. 서비스 중지
sudo systemctl stop ddos-agent

# 2. 서비스 비활성화
sudo systemctl disable ddos-agent

# 3. Agent 스크립트 삭제
sudo rm /usr/local/bin/ddos-agent.sh

# 4. 서비스 파일 삭제
sudo rm /etc/systemd/system/ddos-agent.service

# 5. Systemd 재로드
sudo systemctl daemon-reload
```

---

## ❓ 자주 묻는 질문

### Q1. 이게 뭐하는 건가요?
A: 서버의 DDoS 공격 상태와 트래픽을 중앙에서 실시간으로 모니터링하는 시스템입니다.

### Q2. 서버에 부하가 생기나요?
A: 아니요. 매우 경량 스크립트로 CPU/메모리 사용량이 거의 없습니다.
   - CPU: 0.1% 미만
   - 메모리: 10MB 미만
   - 디스크: 10KB
   - 네트워크: 1KB/30초

### Q3. 보안에 문제는 없나요?
A: 서버 정보를 읽기만 하고, 통계 데이터만 HTTPS로 안전하게 전송합니다.
   민감한 정보는 수집하지 않으며, 파일 접근 권한도 없습니다.

### Q4. 언제 대시보드에 나타나나요?
A: 설치 후 약 30초 이내에 https://ddos.neuralgrid.kr/ 에 자동으로 표시됩니다.

### Q5. 비용이 드나요?
A: 아니요, 완전 무료입니다.

### Q6. 방화벽 설정이 필요한가요?
A: 아니요. Agent가 외부로 HTTPS 요청만 보내므로 별도 설정이 필요 없습니다.
   (단, 아웃바운드 HTTPS가 차단된 환경이라면 443 포트 허용 필요)

### Q7. Windows 서버에서도 되나요?
A: 현재는 Linux 서버만 지원합니다. (Ubuntu, CentOS, Debian, RHEL 등)

---

## 📞 지원

문제가 해결되지 않을 경우:

1. **로그 확인 후 공유**:
   ```bash
   sudo journalctl -u ddos-agent -n 50 > agent-log.txt
   ```
   
2. **연락처**: [담당자 연락처 입력]

3. **이메일**: [이메일 주소 입력]

---

## 🎯 체크리스트

설치 전:
- [ ] SSH로 서버 접속 가능
- [ ] sudo 권한 있음
- [ ] 인터넷 연결 정상

설치 중:
- [ ] 명령어 복사 붙여넣기 완료
- [ ] "설치가 완료되었습니다" 메시지 확인

설치 후:
- [ ] `systemctl status ddos-agent` 실행 시 "active (running)"
- [ ] https://ddos.neuralgrid.kr/ 에서 서버 확인됨

---

**설치가 완료되면 더 이상 할 일이 없습니다!**  
**Agent가 자동으로 모니터링 데이터를 전송합니다.** ✅

---

**문서 버전**: 1.0  
**최종 수정**: 2025-12-15  
**작성자**: NeuralGrid DevOps Team
