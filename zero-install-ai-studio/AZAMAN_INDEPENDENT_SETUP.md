# 🌐 shorts.neuralgrid.kr/azaman 완전 독립 실행 가이드

## ⚠️ 중요: 기존 경로 절대 보호!

**절대 건드리면 안 되는 것:**
- ✅ `http://shorts.neuralgrid.kr/` (포트 8080)
- ✅ 기존 애플리케이션의 모든 설정
- ✅ 기존 PM2/Systemd 프로세스

**새로 추가하는 것:**
- ✅ `/azaman/` 경로 (포트 8081)
- ✅ 완전히 독립된 Next.js 인스턴스
- ✅ 별도의 빌드 및 프로세스

---

## 📋 아키텍처

### 완전 분리된 두 개의 인스턴스

```
┌─────────────────────────────────────────┐
│   shorts.neuralgrid.kr (Nginx)          │
└─────────────────────────────────────────┘
           │
           ├─ / ──────────────────┐
           │                      ▼
           │              ┌───────────────┐
           │              │  포트 8080     │
           │              │  (기존 앱)     │
           │              │  변경 없음!    │
           │              └───────────────┘
           │
           └─ /azaman/ ───────────┐
                                  ▼
                          ┌───────────────┐
                          │  포트 8081     │
                          │  (새 앱)       │
                          │  완전 독립!    │
                          └───────────────┘
```

---

## 🚀 설치 방법

### 자동 설치 (권장)

```bash
cd /home/azamans/webapp/zero-install-ai-studio
sudo bash setup-azaman-independent.sh
```

이 스크립트가 자동으로:
1. ✅ 기존 설정 보호 확인
2. ✅ Azaman 전용 Next.js 빌드 생성
3. ✅ 포트 8081에서 별도 실행
4. ✅ PM2 또는 Systemd 설정
5. ✅ Nginx 설정 업데이트
6. ✅ 모든 서비스 재시작

---

## 🔧 수동 설치 (고급 사용자)

### 1. 기존 설정 확인

```bash
cd /home/azamans/webapp/zero-install-ai-studio

# next.config.js에 basePath가 없는지 확인
grep "basePath" next.config.js
# 결과: (없어야 정상)
```

### 2. Azaman 전용 빌드

```bash
# Azaman 설정 사용
cp next.config.azaman.js next.config.js.temp
mv next.config.js next.config.js.original
mv next.config.js.temp next.config.js

# 빌드
npm run build

# 빌드 파일 이름 변경
mv .next .next-azaman

# 원래 설정 복구
mv next.config.js.original next.config.js
```

### 3. PM2로 시작 (포트 8081)

```bash
# Azaman 인스턴스 시작
pm2 start npm --name "zero-install-azaman" -- start -- -p 8081

# 저장
pm2 save

# 상태 확인
pm2 status
```

### 4. Nginx 설정

```bash
# 백업
sudo cp /etc/nginx/sites-available/shorts \
  /etc/nginx/sites-available/shorts.backup

# 새 설정 적용
sudo cp nginx-shorts-independent.conf \
  /etc/nginx/sites-available/shorts

# 테스트
sudo nginx -t

# 재시작
sudo systemctl restart nginx
```

---

## ✅ 확인 사항

### 1. 포트 확인

```bash
# 기존 앱 (포트 8080)
lsof -i :8080

# Azaman 앱 (포트 8081)
lsof -i :8081
```

### 2. PM2 상태 확인

```bash
pm2 status
```

출력 예시:
```
┌─────┬──────────────────────────┬─────┬────────┐
│ id  │ name                      │ mode│ status │
├─────┼──────────────────────────┼─────┼────────┤
│ 0   │ zero-install-ai-studio   │ fork│ online │ ← 기존 (8080)
│ 1   │ zero-install-azaman      │ fork│ online │ ← 새로 (8081)
└─────┴──────────────────────────┴─────┴────────┘
```

### 3. 웹 브라우저 테스트

```bash
# 기존 경로 테스트
curl -I http://shorts.neuralgrid.kr/

# 새 경로 테스트
curl -I http://shorts.neuralgrid.kr/azaman/
```

---

## 📊 파일 구조

```
zero-install-ai-studio/
├── next.config.js                      # 기존 설정 (basePath 없음)
├── next.config.azaman.js              # Azaman 전용 설정 (basePath 있음)
├── package.json                        # 기존 패키지
├── package.azaman.json                # Azaman 전용 (포트 8081)
├── .next/                             # 기존 빌드 (포트 8080)
├── .next-azaman/                      # Azaman 빌드 (포트 8081)
├── nginx-shorts-independent.conf      # Nginx 설정
├── zero-install-azaman.service        # Systemd 서비스
├── setup-azaman-independent.sh        # 자동 설치 스크립트
└── AZAMAN_INDEPENDENT_SETUP.md        # 이 가이드
```

---

## 🛠️ 관리 명령어

### PM2 사용 시

```bash
# 전체 상태 확인
pm2 status

# 기존 앱 (8080)
pm2 logs zero-install-ai-studio
pm2 restart zero-install-ai-studio
pm2 stop zero-install-ai-studio

# Azaman 앱 (8081)
pm2 logs zero-install-azaman
pm2 restart zero-install-azaman
pm2 stop zero-install-azaman
```

### Systemd 사용 시

```bash
# Azaman 서비스 관리
sudo systemctl status zero-install-azaman
sudo systemctl restart zero-install-azaman
sudo systemctl stop zero-install-azaman
sudo systemctl start zero-install-azaman

# 로그 확인
sudo journalctl -u zero-install-azaman -f
```

---

## 🐛 문제 해결

### 포트 8081이 이미 사용 중

```bash
# 포트 사용 프로세스 확인
lsof -i :8081

# 프로세스 종료 (PID 확인 후)
kill -9 [PID]

# 또는 PM2로 종료
pm2 delete zero-install-azaman
```

### Azaman 경로가 404 오류

**원인**: 빌드가 제대로 되지 않았거나 프로세스가 실행되지 않음

**해결**:
```bash
cd /home/azamans/webapp/zero-install-ai-studio

# 재빌드
cp next.config.azaman.js next.config.js
npm run build
mv .next .next-azaman
git checkout next.config.js

# 재시작
pm2 restart zero-install-azaman
```

### 기존 경로도 작동하지 않음

**원인**: Nginx 설정 문제

**해결**:
```bash
# 백업 복원
sudo cp /etc/nginx/sites-available/shorts.backup \
  /etc/nginx/sites-available/shorts

# Nginx 재시작
sudo systemctl restart nginx
```

### CSS/JS가 로드되지 않음

**원인**: basePath 설정 문제

**확인**:
- 브라우저 개발자 도구 > 네트워크 탭
- `/azaman/_next/...` 경로로 요청되는지 확인
- Nginx에서 `/azaman/_next/` location 블록 확인

---

## 🔄 설정 제거 (롤백)

Azaman 경로를 완전히 제거하려면:

### 1. PM2 프로세스 중지

```bash
pm2 delete zero-install-azaman
pm2 save
```

### 2. Systemd 서비스 중지 (사용 시)

```bash
sudo systemctl stop zero-install-azaman
sudo systemctl disable zero-install-azaman
sudo rm /etc/systemd/system/zero-install-azaman.service
sudo systemctl daemon-reload
```

### 3. Nginx 설정 복원

```bash
sudo cp /etc/nginx/sites-available/shorts.backup \
  /etc/nginx/sites-available/shorts
sudo systemctl restart nginx
```

### 4. 빌드 파일 삭제

```bash
cd /home/azamans/webapp/zero-install-ai-studio
rm -rf .next-azaman
```

---

## 🎯 핵심 포인트

### ✅ 완전 분리됨

- **기존 (8080)**: 독립적으로 작동, 변경 없음
- **새로 (8081)**: 독립적으로 작동, 기존에 영향 없음

### ✅ 각각의 설정

| 항목 | 기존 (8080) | Azaman (8081) |
|------|------------|---------------|
| 포트 | 8080 | 8081 |
| 빌드 | `.next` | `.next-azaman` |
| 설정 | `next.config.js` | `next.config.azaman.js` |
| PM2 이름 | `zero-install-ai-studio` | `zero-install-azaman` |
| URL | `/` | `/azaman/` |
| basePath | 없음 | `/azaman` |

### ✅ 관리 편의성

- 각각 독립적으로 재시작 가능
- 한쪽이 다운되어도 다른 쪽은 정상 작동
- 업데이트 시 한쪽만 수정 가능

---

## 📝 체크리스트

설정 완료 확인:

- [ ] 포트 8080 실행 중 (기존)
- [ ] 포트 8081 실행 중 (Azaman)
- [ ] PM2/Systemd 두 프로세스 모두 online
- [ ] Nginx 설정 업데이트
- [ ] 기존 경로 테스트 (/)
- [ ] 새 경로 테스트 (/azaman/)
- [ ] CSS/JS 정상 로드
- [ ] 모든 기능 정상 작동

---

## 🎉 완료!

**최종 접속 URL**:
- 기존: http://shorts.neuralgrid.kr/ (포트 8080)
- 새로: http://shorts.neuralgrid.kr/azaman/ (포트 8081)

**완전히 독립적으로 작동하며, 서로 영향을 주지 않습니다!** ✅
