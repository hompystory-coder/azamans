# SSH 로그인 실패 IP 자동 차단 시스템

## 📋 스크립트 목록

### 1. `ssh_fail_block.sh` - 메인 차단 스크립트
SSH 로그인 5회 이상 실패한 IP를 iptables로 자동 차단

**실행:**
```bash
sudo /home/mvc/ssh_fail_block.sh
```

**기능:**
- `/var/log/secure` 로그 분석
- 실패 횟수 5회 이상 IP 차단
- 차단 기록 저장 (`/var/log/ssh_blocked_ips.log`)
- 중복 차단 방지

---

### 2. `ssh_unblock_ip.sh` - IP 차단 해제
특정 IP의 차단을 해제

**실행:**
```bash
sudo /home/mvc/ssh_unblock_ip.sh <IP주소>

# 예시
sudo /home/mvc/ssh_unblock_ip.sh 192.168.1.100
```

---

### 3. `ssh_block_status.sh` - 차단 현황 확인
현재 차단된 IP 목록 및 통계 확인

**실행:**
```bash
sudo /home/mvc/ssh_block_status.sh
```

---

## ⚙️ 자동 실행 설정 (Cron)

### 10분마다 자동 실행
```bash
sudo crontab -e

# 다음 줄 추가
*/10 * * * * /home/mvc/ssh_fail_block.sh >> /var/log/ssh_block_cron.log 2>&1
```

### 1시간마다 자동 실행
```bash
0 * * * * /home/mvc/ssh_fail_block.sh >> /var/log/ssh_block_cron.log 2>&1
```

### 매일 자정에 실행
```bash
0 0 * * * /home/mvc/ssh_fail_block.sh >> /var/log/ssh_block_cron.log 2>&1
```

---

## 🔧 커스터마이징

### 차단 기준 변경 (ssh_fail_block.sh)
```bash
# 5회 → 3회로 변경
THRESHOLD=3
```

### 로그 파일 경로 변경
```bash
# Ubuntu/Debian
LOG_FILE="/var/log/auth.log"

# CentOS/RHEL
LOG_FILE="/var/log/secure"
```

---

## 📊 로그 파일

| 파일 | 설명 |
|------|------|
| `/var/log/ssh_blocked_ips.log` | 차단된 IP 기록 |
| `/var/log/ssh_block_cron.log` | Cron 실행 로그 |
| `/var/log/secure` | SSH 로그인 로그 (CentOS) |

---

## 🛡️ 수동 관리

### 전체 차단 목록 확인
```bash
sudo iptables -L INPUT -n --line-numbers
```

### 특정 IP 수동 차단
```bash
sudo iptables -I INPUT -s 192.168.1.100 -j DROP
```

### 특정 IP 수동 차단 해제
```bash
sudo iptables -D INPUT -s 192.168.1.100 -j DROP
```

### 모든 차단 해제
```bash
sudo iptables -F INPUT
```

### iptables 규칙 영구 저장 (CentOS 7)
```bash
sudo service iptables save
```

### iptables 규칙 영구 저장 (CentOS 8)
```bash
sudo iptables-save > /etc/sysconfig/iptables
```

---

## ⚠️ 주의사항

1. **자기 IP 차단 방지**: 본인 IP가 차단되지 않도록 주의
2. **화이트리스트 설정**: 신뢰할 수 있는 IP는 사전 허용
3. **로그 확인**: 정기적으로 차단 로그 확인
4. **재부팅 후**: iptables 규칙이 초기화될 수 있으므로 영구 저장 필요

---

## 🔐 화이트리스트 IP 설정

신뢰할 수 있는 IP는 차단되지 않도록 사전 허용:

```bash
# 특정 IP 허용
sudo iptables -I INPUT -s 192.168.1.50 -j ACCEPT

# 특정 서브넷 허용
sudo iptables -I INPUT -s 192.168.1.0/24 -j ACCEPT
```

---

## 📞 문제 해결

### 문제: 본인 IP가 차단됨
```bash
# 콘솔 접속 후
sudo iptables -D INPUT -s <본인IP> -j DROP
```

### 문제: 스크립트 실행 권한 없음
```bash
sudo chmod +x /home/mvc/ssh_*.sh
```

### 문제: 로그 파일 없음
```bash
# CentOS/RHEL
sudo tail -f /var/log/secure

# Ubuntu/Debian
sudo tail -f /var/log/auth.log
```

---

## 📈 모니터링

### 실시간 차단 로그 확인
```bash
sudo tail -f /var/log/ssh_blocked_ips.log
```

### SSH 로그인 실패 실시간 확인
```bash
sudo tail -f /var/log/secure | grep "Failed password"
```

### 차단된 IP 수 확인
```bash
sudo iptables -L INPUT -n | grep -c "DROP"
```
