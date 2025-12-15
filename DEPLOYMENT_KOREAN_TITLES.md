# 메인 페이지 한글+영어 제목 업데이트 배포

## 📋 변경 사항

### 서비스 제목 표시 형식 변경
- **이전**: 영어 제목만 표시 (예: "Blog Shorts Generator")
- **이후**: **한글 제목 (크게)** + 영어 제목 (작게 회색)

### 적용된 서비스
1. **블로그 기사 쇼츠생성기** (Blog Shorts Generator) - bn-shop.neuralgrid.kr
2. **쇼츠 영상 자동화** (MediaFX Shorts) - mfx.neuralgrid.kr
3. **스타뮤직** (NeuronStar Music) - music.neuralgrid.kr
4. **쿠팡쇼츠** (Shorts Market) - market.neuralgrid.kr
5. **N8N 워크플로우 자동화** (N8N Automation) - n8n.neuralgrid.kr
6. **서버모니터링** (System Monitor) - monitor.neuralgrid.kr

## 🎨 UI 개선 사항

### CSS 스타일링
```css
.service-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
}

.service-title-en {
    font-size: 0.85rem;          /* 작은 크기 */
    font-weight: 400;            /* 일반 두께 */
    display: block;              /* 새 줄에 표시 */
    color: var(--text-muted);    /* 회색 텍스트 */
}
```

### HTML 구조
```javascript
<h3 class="service-title">
    ${serviceInfo.titleKo || service.name}
    ${serviceInfo.titleEn ? `<span class="service-title-en">${serviceInfo.titleEn}</span>` : ''}
</h3>
```

## 📊 변경 통계

| 항목 | 값 |
|------|-----|
| 수정된 파일 | neuralgrid-main-page.html |
| 파일 크기 | 44KB (45,014 bytes) |
| 추가된 필드 | titleKo, titleEn (각 서비스당) |
| CSS 라인 추가 | ~6 lines |
| JS 데이터 추가 | 12 lines (6 services × 2 fields) |

## 🚀 배포 방법

### 방법 1: 수동 배포 (Sudo 필요)
```bash
cd /home/azamans/webapp

# 백업 생성
sudo cp /var/www/neuralgrid.kr/html/index.html \
        /var/www/neuralgrid.kr/html/index.html.backup_korean_titles

# 새 버전 배포
sudo cp neuralgrid-main-page.html \
        /var/www/neuralgrid.kr/html/index.html

# 권한 설정
sudo chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
sudo chmod 644 /var/www/neuralgrid.kr/html/index.html

# 확인
ls -lh /var/www/neuralgrid.kr/html/index.html
```

### 방법 2: 배포 스크립트 사용
```bash
cd /home/azamans/webapp
./deploy_main.sh
```

### 방법 3: 임시 파일 경로 사용
```bash
# 파일이 이미 준비됨
/tmp/deploy-korean-titles.html

# 관리자가 수동으로 복사
sudo cp /tmp/deploy-korean-titles.html /var/www/neuralgrid.kr/html/index.html
sudo chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
```

## ✅ 배포 검증

### 1. 파일 크기 확인
```bash
ls -lh /var/www/neuralgrid.kr/html/index.html
# 예상: 44K (45,014 bytes)
```

### 2. 한글 제목 확인
```bash
curl -s "https://neuralgrid.kr/?t=$(date +%s)" | grep -o "titleKo\|titleEn\|블로그 기사 쇼츠생성기\|쇼츠 영상 자동화\|스타뮤직\|쿠팡쇼츠"
```

### 3. 브라우저 검증
- https://neuralgrid.kr 접속
- 각 서비스 카드에서 **한글 제목 (큰 굵은 글씨)** 확인
- 그 아래 **영어 제목 (작은 회색 글씨)** 확인

## 📝 변경 예시

### Before (이전)
```
━━━━━━━━━━━━━━━━━━━━
│ Blog Shorts Generator │  ← 영어만
━━━━━━━━━━━━━━━━━━━━
```

### After (이후)
```
━━━━━━━━━━━━━━━━━━━━━━━━━
│ 블로그 기사 쇼츠생성기 │  ← 한글 (크게, 굵게)
│ Blog Shorts Generator │  ← 영어 (작게, 회색)
━━━━━━━━━━━━━━━━━━━━━━━━━
```

## 🔧 기술 상세

### servicesData 객체 구조
```javascript
'Blog Shorts Generator': {
    icon: '📰',
    titleKo: '블로그 기사 쇼츠생성기',    // 신규 추가
    titleEn: 'Blog Shorts Generator',    // 신규 추가
    url: 'https://bn-shop.neuralgrid.kr',
    description: '블로그 기사를 AI가 자동으로...',
    features: [...],
    pricing: '영상당 약 $0.06'
}
```

### 렌더링 로직
```javascript
// loadServices 함수 내부
cardHTML += `
    <h3 class="service-title">
        ${serviceInfo.titleKo || service.name}
        ${serviceInfo.titleEn ? 
            `<span class="service-title-en">${serviceInfo.titleEn}</span>` 
            : ''
        }
    </h3>
`;
```

## 📈 기대 효과

1. **사용자 경험 개선**
   - 한국 사용자에게 친숙한 한글 제목 우선 표시
   - 영어 제목으로 정확한 서비스명 확인 가능

2. **시각적 계층 구조**
   - 한글 제목 (1.5rem, bold) → 주요 정보
   - 영어 제목 (0.85rem, normal) → 보조 정보

3. **브랜드 일관성**
   - 한국 시장 타겟팅 강화
   - 글로벌 서비스명 병기로 전문성 유지

## 🎯 완료 상태

- ✅ CSS 스타일 추가 완료
- ✅ servicesData에 titleKo/titleEn 추가 완료  
- ✅ 렌더링 로직 업데이트 완료
- ✅ 로컬 파일 준비 완료 (44KB)
- ⏳ 프로덕션 배포 대기 (sudo 권한 필요)

## 📅 작업 정보

- **작성일**: 2025-12-15 09:36 UTC
- **파일 위치**: /home/azamans/webapp/neuralgrid-main-page.html
- **임시 배포 파일**: /tmp/deploy-korean-titles.html
- **백업 명**: index.html.backup_korean_titles

---

**⚠️ 참고**: 프로덕션 배포를 위해서는 sudo 권한이 필요합니다.  
배포 후 https://neuralgrid.kr 에서 한글+영어 제목이 정상적으로 표시되는지 확인하세요.
