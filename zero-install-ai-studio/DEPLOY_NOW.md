# 🎯 Shorts Azaman 독립 인스턴스 - 최종 배포 가이드

## ✅ 완료 상태

### 개발 환경 (완료)
- ✅ Next.js 설정 (basePath: '/azaman')
- ✅ TypeScript 빌드 에러 수정
- ✅ 프로덕션 빌드 완료 (.next/ 디렉토리)
- ✅ PM2 프로세스 실행 (zero-install-azaman, 포트 3000)
- ✅ Nginx 설정 파일 준비
- ✅ 자동 배포 스크립트 준비
- ✅ Git 커밋 및 푸시 완료

### 프로덕션 배포 (대기 중)
- 🟡 Nginx 설정 적용 필요
- 🟡 접속 테스트 필요

## 🚀 프로덕션 배포 명령어

### 1단계: 서버 접속 및 배포 스크립트 실행

```bash
# 서버에 접속 후
cd /home/azamans/webapp/zero-install-ai-studio
sudo bash deploy-azaman-final.sh
```

### 2단계: 접속 확인

브라우저에서 다음 URL로 접속:
- 기존 서비스 (변경 없음): https://shorts.neuralgrid.kr/
- Azaman 서비스 (신규): https://shorts.neuralgrid.kr/azaman/

## 📊 아키텍처 요약

```
외부 접속 (포트 80/443 ONLY)
         ↓
    [Nginx 리버스 프록시]
         ↓
    ┌────┴────┐
    ↓         ↓
포트 3006   포트 3000
(기존)     (Azaman)
```

### 경로 매핑
| 외부 URL | 내부 포트 | 설명 |
|---------|----------|------|
| `https://shorts.neuralgrid.kr/` | 3006 | 기존 서비스 (절대 변경 없음) |
| `https://shorts.neuralgrid.kr/azaman/` | 3000 | 신규 독립 인스턴스 |

## 🔧 관리 명령어

### PM2 관리
```bash
# 상태 확인
pm2 status

# Azaman 로그
pm2 logs zero-install-azaman

# 재시작
pm2 restart zero-install-azaman
```

### Nginx 관리
```bash
# 상태 확인
sudo systemctl status nginx

# 재시작
sudo systemctl reload nginx

# 로그 확인
sudo tail -f /var/log/nginx/shorts.neuralgrid.kr.error.log
```

## 🛡️ 중요 보장 사항

### ✅ 기존 서비스 보호
- `https://shorts.neuralgrid.kr/` (포트 3006) **절대 변경 없음**
- 기존 Nginx 설정의 루트 `/` 경로는 그대로 유지
- 기존 PM2 프로세스와 완전 분리

### ✅ 완전 독립 운영
- 별도 포트 (3000 vs 3006)
- 별도 PM2 프로세스
- 별도 빌드 디렉토리
- 별도 설정 파일

### ✅ 포트 80만 사용
- 외부에는 **포트 80/443만** 노출
- 내부 포트는 localhost만 접근 가능
- Nginx가 리버스 프록시 역할

## 📂 주요 파일

| 파일 | 설명 |
|------|------|
| `nginx-shorts-azaman-final.conf` | Nginx 설정 (두 인스턴스 프록시) |
| `deploy-azaman-final.sh` | 자동 배포 스크립트 |
| `AZAMAN_DEPLOYMENT_FINAL.md` | 상세 배포 가이드 |
| `next.config.js` | Azaman 설정 (basePath: '/azaman') |

## 🐛 문제 해결

### 502 Bad Gateway
```bash
pm2 restart zero-install-azaman
lsof -i :3000
```

### 404 Not Found
```bash
cd /home/azamans/webapp/zero-install-ai-studio
npm run build
pm2 restart zero-install-azaman
```

### Nginx 오류
```bash
sudo nginx -t
sudo systemctl reload nginx
```

## 📞 지원

- **문서**: `AZAMAN_DEPLOYMENT_FINAL.md` 참고
- **로그**: `pm2 logs zero-install-azaman`
- **Nginx 로그**: `/var/log/nginx/shorts.neuralgrid.kr.error.log`

## 🎉 다음 단계

1. 서버에서 `sudo bash deploy-azaman-final.sh` 실행
2. 브라우저에서 두 URL 접속 확인
3. PM2와 Nginx 로그 모니터링

---

**중요**: 기존 `https://shorts.neuralgrid.kr/` 서비스는 **절대 변경되지 않았습니다**!
