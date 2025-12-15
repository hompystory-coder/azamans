# 🚀 서버 배포 지침

## 1. 업데이트할 파일 다운로드
```bash
# GitHub에서 최신 코드 가져오기
cd ~/shorts-market
git pull origin genspark_ai_developer_clean
```

## 2. standalone-server.js 교체
```bash
# 새 서버 파일을 직접 복사
cp standalone-server.js ~/shorts-market/standalone-server.js
```

## 3. PM2 재시작
```bash
cd ~/shorts-market
pm2 restart shorts-market
pm2 logs shorts-market --nostream --lines 20
```

## 4. 확인
```bash
# API 테스트
curl -s https://market.neuralgrid.kr/api/shorts | jq '.data.shorts | length'
curl -s https://market.neuralgrid.kr/api/admin/stats | jq '.data'
```

## 📦 주요 변경사항

### API 응답 구조 수정
모든 API가 일관된 구조로 응답하도록 수정:

#### Before:
```json
{
  "success": true,
  "data": [...]  // 배열 직접 반환
}
```

#### After:
```json
{
  "success": true,
  "data": {
    "shorts": [...],  // 객체로 래핑
    "count": 42
  }
}
```

### 수정된 엔드포인트:
- ✅ GET /api/shorts → data.shorts
- ✅ GET /api/shorts/status/:status → data.shorts
- ✅ GET /api/shorts/categories/list → data.categories
- ✅ GET /api/admin/shorts/:status → data.shorts
- ✅ GET /api/admin/creators → data.creators
- ✅ GET /api/admin/stats → data.{totalShorts, pendingShorts...}
- ✅ GET /api/admin/users → data.users

## 🎯 테스트 체크리스트

### 홈페이지
- [ ] 쇼츠 목록이 제대로 표시되는지
- [ ] 검색 기능이 작동하는지
- [ ] 카테고리 필터가 작동하는지
- [ ] 쇼츠 카드 클릭 시 상세 페이지로 이동하는지

### 로그인
- [ ] 로그인이 정상적으로 되는지
- [ ] 로그인 후 사용자 정보가 표시되는지
- [ ] 관리자 계정으로 로그인 시 '관리자' 버튼이 보이는지

### 관리자 페이지
- [ ] 쇼츠 승인/거절이 작동하는지
- [ ] 크리에이터 목록이 보이는지
- [ ] 통계가 제대로 표시되는지

### 마이페이지
- [ ] API 설정이 저장되는지
- [ ] 내 쇼츠 목록이 보이는지
- [ ] 쇼츠 수동 등록이 작동하는지

### 크리에이터 페이지
- [ ] 크리에이터 등록 폼이 작동하는지

### 쇼츠 상세 페이지
- [ ] 쇼츠 정보가 제대로 표시되는지
- [ ] YouTube 영상이 임베드되는지
- [ ] 구매 버튼이 작동하는지
- [ ] 클릭 추적이 작동하는지

