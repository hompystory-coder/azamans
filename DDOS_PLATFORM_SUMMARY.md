# 🛡️ NeuralGrid Security & Performance Platform

## 📋 프로젝트 개요

DDoS 부하 테스터를 **보안 & 성능 테스트 통합 플랫폼**으로 확장했습니다.

### ⚡ 주요 업데이트

**기존**: 단순 DDoS 부하 테스트만 제공
**신규**: 웹 기반 종합 보안 관리 플랫폼

---

## ✨ 새로운 기능

### 1. 🔥 DDoS 부하 테스터 (기존 + 개선)
- 동시 요청 1000개 지원
- 실시간 TPS & 응답시간 차트
- GET/POST 메서드 지원
- 성공/실패 통계

### 2. 🛡️ IP 차단 관리 (신규)
- **멀티 플랫폼 지원**: CentOS 7, Ubuntu, Debian
- **자동 방화벽 감지**: iptables, firewalld, ufw
- GUI 기반 쉬운 관리
- IP 주소/범위(CIDR) 차단/해제
- 차단 사유 기록 및 관리

### 3. 🌐 도메인 기반 차단 (신규)
- DNS 조회 자동화
- 도메인 → IP 변환 후 차단
- 멀티 IP 도메인 지원
- 차단 내역 추적

### 4. 🌍 국가별 IP 차단 (신규, Beta)
- GeoIP 데이터베이스 통합 준비 완료
- 대량 IP 범위 차단 지원
- 국가 단위 트래픽 관리

---

## 🎨 사용자 인터페이스

### 탭 기반 직관적 UI
1. **DDoS Tester**: 부하 테스트 도구
2. **IP Manager**: IP 차단/해제 관리
3. **Domain Blocker**: 도메인 기반 차단
4. **Geo Blocker**: 국가별 차단 (곧 출시)

### 디자인 특징
- 다크 테마 + Glassmorphism
- 반응형 디자인 (모바일 지원)
- 실시간 차트 (Chart.js)
- 애니메이션 효과

---

## 🖥️ 기술 스택

### 프론트엔드
- HTML5 + CSS3 + Vanilla JavaScript
- Chart.js (실시간 차트)
- Inter 폰트

### 백엔드
- Node.js + Express
- 멀티 플랫폼 시스템 명령어
- DNS 조회 (native dns module)
- JSON 파일 기반 데이터 저장

### 방화벽 통합
- **iptables** (Universal)
- **firewalld** (CentOS 7+, RHEL)
- **ufw** (Ubuntu, Debian)

---

## 📦 배포 정보

### 서버
- URL: https://ddos.neuralgrid.kr
- 포트: 3105
- PM2 프로세스: `ddos-security`

### 파일 구조
```
/var/www/ddos.neuralgrid.kr/
├── index.html (ddos-ip-manager.html)
├── server.js (ddos-ip-manager-server.js)
├── node_modules/
└── /var/lib/neuralgrid/
    ├── blocked-ips.json
    └── blocked-domains.json
```

---

## 🔧 사용 가이드

### IP 차단
1. **IP Manager** 탭 클릭
2. IP 주소 입력 (예: `192.168.1.100` 또는 `192.168.1.0/24`)
3. 차단 사유 입력 (선택)
4. **🚫 IP 차단** 버튼 클릭

### 도메인 차단
1. **Domain Blocker** 탭 클릭
2. 도메인 입력 (예: `malicious-site.com`)
3. **🔍 조회** 버튼으로 IP 확인
4. **🚫 차단** 버튼으로 해당 도메인의 모든 IP 차단

### 차단 해제
- IP 목록에서 **🔓 해제** 버튼 클릭

---

## 🎯 핵심 개선 사항

### 사용자 피드백 반영
1. ✅ **CentOS 7 지원**: 사용자 요청사항
2. ✅ **GUI 관리**: 비개발자도 쉽게 사용
3. ✅ **도메인 차단**: 운영자 니즈 반영
4. ✅ **국가별 차단**: 대량 트래픽 관리 요구

### 기술적 개선
1. ✅ 멀티 플랫폼 자동 감지
2. ✅ 파일 기반 영속성
3. ✅ 방화벽 자동 동기화
4. ✅ 실시간 상태 모니터링

---

## 📈 향후 계획

### 단기 (1-2주)
- [ ] GeoIP 데이터베이스 통합 완료
- [ ] 통계 대시보드 추가
- [ ] 알림 기능 (이메일/Slack)

### 중기 (1-2개월)
- [ ] API 키 기반 인증
- [ ] 멀티 서버 관리
- [ ] 로그 분석 기능

### 장기
- [ ] AI 기반 위협 탐지
- [ ] 자동 차단 규칙
- [ ] 클라우드 연동 (AWS, GCP)

---

## 🔗 관련 링크

- **서비스 URL**: https://ddos.neuralgrid.kr
- **홈페이지**: https://neuralgrid.kr
- **Git Repo**: https://github.com/hompystory-coder/azamans
- **Branch**: `genspark_ai_developer_clean`
- **Commit**: `05e9265`

---

## 📝 변경 이력

### 2025-12-15
- ✅ IP 관리 대시보드 개발 완료
- ✅ 도메인 차단 기능 추가
- ✅ CentOS 7 호환성 확보
- ✅ neuralgrid.kr 홈페이지 카드 업데이트
- ✅ 서버 배포 완료

---

**Made with ❤️ by NeuralGrid Team**
