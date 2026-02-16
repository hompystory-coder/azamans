# 🚀 NeuralGrid 메인 페이지 업데이트 가이드

## 📋 변경 사항 요약

**블로그 기사 쇼츠생성기** PR 카드를 실제 제품 분석 결과에 맞게 업데이트했습니다.

### 🔄 주요 변경사항

| 항목 | 변경 전 | 변경 후 |
|------|---------|---------|
| **URL** | `https://bn-shop.neuralgrid.kr` ❌ | `https://shorts.neuralgrid.kr` ✅ |
| **처리 시간** | 4분 | 15초 (실제 측정) |
| **비용** | $0.06 | ₩29 (정확한 계산) |
| **설명** | Gemini 2.0, Pollinations.AI 언급 | 실제 사용 기술 (Minimax TTS, FFmpeg) |

### 📝 업데이트된 내용

#### 새로운 설명:
```
📰 블로그 글을 단 15초 만에 유튜브 쇼츠로! 
URL만 입력하면 AI가 자동으로 크롤링부터 영상 제작까지 완전 자동화. 
Minimax 고품질 한국어 TTS로 자연스러운 나레이션 생성.
```

#### 새로운 기능 목록:
1. 🔗 URL 한 번으로 완전 자동화 (6단계 워크플로우)
2. 📝 템플릿 기반 스마트 스크립트 생성 (무료)
3. 🎙️ Minimax TTS 고품질 한국어 음성 (자연스러운 구어체)
4. 🎬 FFmpeg 로컬 렌더링 (자막 자동 삽입)
5. 💾 외장 하드 저장소 (3.6TB, 무제한 생성)
6. ⚡ 초고속 처리: 평균 10-15초 이내 완성

#### 새로운 가격:
```
💰 영상당 단돈 ₩29 (초저가, Minimax TTS만 유료)
```

---

## 🛠️ 배포 방법

### 방법 1: 자동 배포 스크립트 (권장)

```bash
cd /home/azamans/webapp
sudo bash deploy-neuralgrid-main.sh
```

이 스크립트는 자동으로:
- ✅ 현재 파일 백업
- ✅ 업데이트된 파일 배포
- ✅ 권한 설정
- ✅ Nginx 테스트 및 리로드

### 방법 2: 수동 배포

```bash
# 1. 백업 생성
sudo cp /var/www/neuralgrid.kr/html/index.html \
       /var/www/neuralgrid.kr/html/index.html.backup_$(date +%Y%m%d_%H%M%S)

# 2. 업데이트된 파일 복사
sudo cp /home/azamans/webapp/neuralgrid-main-page-updated.html \
       /var/www/neuralgrid.kr/html/index.html

# 3. 권한 설정
sudo chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
sudo chmod 664 /var/www/neuralgrid.kr/html/index.html

# 4. Nginx 리로드
sudo systemctl reload nginx
```

### 방법 3: Git을 통한 배포 (추천)

```bash
# 1. 변경사항 커밋
cd /home/azamans/webapp
git add neuralgrid-main-page-updated.html
git commit -m "fix: Update blog shorts generator PR card with accurate info"

# 2. 배포 디렉토리에서 풀
cd /var/www/neuralgrid.kr/html
sudo git pull origin main  # 또는 해당 브랜치
```

---

## ✅ 배포 확인

배포 후 다음 명령어로 확인:

```bash
# 1. URL 변경 확인
curl -s https://neuralgrid.kr/ | grep "shorts.neuralgrid.kr"

# 2. 가격 변경 확인
curl -s https://neuralgrid.kr/ | grep "₩29"

# 3. 브라우저에서 확인
# https://neuralgrid.kr/ 접속 → "블로그 기사 쇼츠생성기" 카드 확인
```

---

## 📊 변경 이유

### 1. **URL 오류 수정**
- ❌ `bn-shop.neuralgrid.kr` (BN Shop 주소)
- ✅ `https://shorts.neuralgrid.kr` (실제 제품 주소)

### 2. **실제 기술 스택 반영**
- ❌ Gemini 2.0 (실제로 사용 안 함)
- ❌ Pollinations.AI (사용 안 함)
- ❌ Kling v2.1 Pro (사용 안 함)
- ✅ Minimax TTS API (실제 음성 생성)
- ✅ FFmpeg (실제 비디오 렌더링)
- ✅ 템플릿 기반 스크립트 (무료)

### 3. **정확한 비용 정보**
- 실제 비용 분석: `/home/azamans/shorts-creator-pro/COST_ANALYSIS_DETAILED.md`
- Minimax TTS: ₩29 (150자 기준)
- 나머지: ₩0 (로컬 처리)

### 4. **정확한 처리 시간**
- 실제 측정: 10-15초 (크롤링 → 스크립트 → 음성 → 비디오)
- 이전 표기 "4분"은 과장됨

---

## 🔍 검증 자료

- **제품 URL**: https://shorts.neuralgrid.kr/
- **비용 분석**: `/home/azamans/shorts-creator-pro/COST_ANALYSIS_DETAILED.md`
- **백엔드 코드**: `/home/azamans/shorts-creator-pro/backend/src/routes/`
- **프론트엔드 코드**: `/home/azamans/shorts-creator-pro/frontend/src/pages/`

---

## 📞 문의

배포 과정에서 문제가 발생하면:
1. 백업 파일로 복구: `sudo cp /var/www/neuralgrid.kr/html/index.html.backup_* /var/www/neuralgrid.kr/html/index.html`
2. Nginx 로그 확인: `sudo tail -f /var/log/nginx/error.log`
3. 권한 확인: `ls -la /var/www/neuralgrid.kr/html/index.html`

---

*최종 업데이트: 2024-12-22*
*작성자: AI Assistant*
