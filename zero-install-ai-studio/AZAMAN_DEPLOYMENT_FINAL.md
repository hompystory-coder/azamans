# Shorts Azaman 독립 인스턴스 배포 가이드

## 🎯 목표
- `https://shorts.neuralgrid.kr/azaman/` 경로에 완전히 독립적인 인스턴스 추가
- 기존 `https://shorts.neuralgrid.kr/` 서비스는 **절대 변경하지 않음**
- **포트 80만 외부 접속에 사용** (Nginx 리버스 프록시)

## 📋 아키텍처

```
외부 접속 (포트 80/443)
         ↓
    [Nginx 리버스 프록시]
         ↓
    ┌────┴────┐
    ↓         ↓
포트 3006   포트 3000
(기존)     (Azaman)
```

### 경로 매핑
- `https://shorts.neuralgrid.kr/` → 포트 3006 (기존 서비스, 변경 없음)
- `https://shorts.neuralgrid.kr/azaman/` → 포트 3000 (신규 독립 인스턴스)

## 🚀 자동 배포 (권장)

```bash
cd /home/azamans/webapp/zero-install-ai-studio
sudo bash deploy-azaman-final.sh
```

이 스크립트는 다음을 수행합니다:
1. Nginx 설정 백업
2. 새 설정 적용
3. Nginx 테스트 및 재시작
4. PM2 프로세스 확인
5. 포트 및 접속 테스트

## 📝 수동 배포

### 1. Nginx 설정 업데이트

```bash
# 백업
sudo cp /etc/nginx/sites-available/shorts.neuralgrid.kr \
     /etc/nginx/sites-available/shorts.neuralgrid.kr.backup

# 새 설정 복사
sudo cp /home/azamans/webapp/zero-install-ai-studio/nginx-shorts-azaman-final.conf \
     /etc/nginx/sites-available/shorts.neuralgrid.kr

# 검증
sudo nginx -t

# 재시작
sudo systemctl reload nginx
```

### 2. PM2 프로세스 확인

```bash
pm2 status
pm2 logs zero-install-azaman --lines 20 --nostream
```

### 3. 포트 확인

```bash
# 포트 3000 (Azaman)
lsof -i :3000

# 포트 3006 (기존)
lsof -i :3006
```

## ✅ 검증

### 1. 기존 서비스 확인
```bash
curl -I https://shorts.neuralgrid.kr/
```
응답: `HTTP/2 200` (정상 작동 확인)

### 2. Azaman 서비스 확인
```bash
curl -I https://shorts.neuralgrid.kr/azaman/
```
응답: `HTTP/2 200` (정상 작동 확인)

### 3. 브라우저 테스트
- 기존: https://shorts.neuralgrid.kr/
- Azaman: https://shorts.neuralgrid.kr/azaman/

## 🔧 관리 명령어

### PM2 관리
```bash
# 상태 확인
pm2 status

# Azaman 인스턴스 로그
pm2 logs zero-install-azaman

# 재시작
pm2 restart zero-install-azaman

# 중지
pm2 stop zero-install-azaman

# 시작
pm2 start zero-install-azaman
```

### Nginx 관리
```bash
# 상태 확인
sudo systemctl status nginx

# 재시작
sudo systemctl reload nginx

# 로그 확인
sudo tail -f /var/log/nginx/shorts.neuralgrid.kr.error.log
sudo tail -f /var/log/nginx/shorts.neuralgrid.kr.access.log
```

## 🛡️ 중요 사항

### ⚠️ 기존 서비스 보호
- **절대 변경 금지**: `https://shorts.neuralgrid.kr/` (포트 3006)
- Nginx 설정에서 루트 `/` 경로는 항상 포트 3006으로 프록시
- 기존 서비스의 설정, 포트, 프로세스는 전혀 건드리지 않음

### ✅ 독립성 보장
- 각 인스턴스는 **별도 포트**에서 실행
- 각 인스턴스는 **별도 PM2 프로세스**
- 각 인스턴스는 **독립적인 빌드** (.next 디렉토리)
- 각 인스턴스는 **독립적인 설정** (next.config.js)

### 🔒 보안
- 외부에는 **포트 80/443만** 노출
- 내부 포트 3000, 3006은 localhost만 접근 가능
- SSL/TLS는 Nginx에서 처리 (Let's Encrypt)

## 📂 파일 구조

```
zero-install-ai-studio/
├── next.config.js                      # Azaman 설정 (basePath: '/azaman')
├── .next/                              # Azaman 빌드 결과
├── nginx-shorts-azaman-final.conf      # Nginx 설정
├── deploy-azaman-final.sh              # 자동 배포 스크립트
└── AZAMAN_DEPLOYMENT_FINAL.md          # 이 문서
```

## 🔄 업데이트 프로세스

코드 업데이트 시:

```bash
cd /home/azamans/webapp/zero-install-ai-studio

# 1. 코드 변경 후 빌드
npm run build

# 2. PM2 재시작
pm2 restart zero-install-azaman

# 3. 확인
pm2 logs zero-install-azaman --lines 20 --nostream
```

## 🐛 문제 해결

### 502 Bad Gateway
```bash
# PM2 프로세스 확인
pm2 status

# 프로세스가 중지되어 있으면 시작
pm2 start zero-install-azaman

# 포트 확인
lsof -i :3000
```

### 404 Not Found (Azaman 경로)
```bash
# Next.js 빌드 확인
cd /home/azamans/webapp/zero-install-ai-studio
ls -la .next/

# 빌드가 없으면 다시 빌드
npm run build

# PM2 재시작
pm2 restart zero-install-azaman
```

### Nginx 설정 오류
```bash
# 설정 검증
sudo nginx -t

# 백업에서 복구
sudo cp /etc/nginx/sites-available/shorts.neuralgrid.kr.backup \
     /etc/nginx/sites-available/shorts.neuralgrid.kr

# 재시작
sudo systemctl reload nginx
```

## 📊 현재 상태

- ✅ Next.js 빌드 완료 (.next/ 디렉토리 생성)
- ✅ PM2 프로세스 실행 중 (zero-install-azaman, 포트 3000)
- ✅ Nginx 설정 파일 생성 완료
- 🟡 Nginx 설정 적용 대기 (sudo bash deploy-azaman-final.sh 실행 필요)

## 🎉 완료 체크리스트

- [x] Next.js 설정 (basePath, assetPrefix)
- [x] TypeScript/ESLint 에러 처리
- [x] 프로덕션 빌드
- [x] PM2 프로세스 시작 (포트 3000)
- [x] Nginx 설정 파일 생성
- [x] 배포 스크립트 작성
- [ ] **Nginx 설정 적용 (서버에서 실행)**
- [ ] **접속 테스트 (브라우저)**

## 🚦 다음 단계

서버에서 실행:
```bash
cd /home/azamans/webapp/zero-install-ai-studio
sudo bash deploy-azaman-final.sh
```

그 다음 브라우저에서 테스트:
- https://shorts.neuralgrid.kr/ (기존, 변경 없음)
- https://shorts.neuralgrid.kr/azaman/ (신규, 독립 실행)
