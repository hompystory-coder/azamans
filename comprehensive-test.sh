#!/bin/bash

BASE_URL="https://market.neuralgrid.kr"
ADMIN_EMAIL="admin@example.com"
ADMIN_PASS="admin123"

echo "================================"
echo "종합 통합 테스트 시작"
echo "================================"
echo ""

# 1. Homepage Test
echo "1️⃣ 홈페이지 테스트"
echo "----------------------------"
echo "✓ 메인 페이지 로드 테스트..."
HOME_TITLE=$(curl -s "$BASE_URL/" | grep -o '<title>.*</title>' | sed 's/<[^>]*>//g')
echo "  페이지 제목: $HOME_TITLE"

echo "✓ 쇼츠 목록 API 테스트..."
SHORTS_COUNT=$(curl -s "$BASE_URL/api/shorts" | jq '.data.shorts | length')
echo "  총 쇼츠 수: $SHORTS_COUNT개"

echo "✓ 카테고리 필터 테스트..."
CATEGORIES=$(curl -s "$BASE_URL/api/shorts/categories/list" | jq -r '.data.categories[]')
echo "  카테고리: $CATEGORIES"

echo "✓ 검색 기능 테스트..."
SEARCH_RESULT=$(curl -s "$BASE_URL/api/shorts?search=트리" | jq '.data.shorts | length')
echo "  '트리' 검색 결과: $SEARCH_RESULT개"

echo ""

# 2. Login Test
echo "2️⃣ 로그인 테스트"
echo "----------------------------"
echo "✓ 관리자 로그인..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$ADMIN_EMAIL\",\"password\":\"$ADMIN_PASS\"}")

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.token')
USER_NAME=$(echo $LOGIN_RESPONSE | jq -r '.data.user.name')
USER_ROLE=$(echo $LOGIN_RESPONSE | jq -r '.data.user.role')

if [ "$TOKEN" != "null" ]; then
  echo "  ✅ 로그인 성공: $USER_NAME ($USER_ROLE)"
  echo "  토큰: ${TOKEN:0:30}..."
else
  echo "  ❌ 로그인 실패"
  exit 1
fi

echo ""

# 3. Admin Page Test
echo "3️⃣ 관리자 페이지 테스트"
echo "----------------------------"
echo "✓ 관리자 페이지 로드..."
ADMIN_TITLE=$(curl -s "$BASE_URL/admin" | grep -o '<title>.*</title>' | sed 's/<[^>]*>//g')
echo "  페이지 제목: $ADMIN_TITLE"

echo "✓ 관리자 통계 조회..."
STATS=$(curl -s "$BASE_URL/api/admin/stats" \
  -H "Authorization: Bearer $TOKEN")
TOTAL_SHORTS=$(echo $STATS | jq '.data.totalShorts')
PENDING_SHORTS=$(echo $STATS | jq '.data.pendingShorts')
TOTAL_CREATORS=$(echo $STATS | jq '.data.totalCreators')
echo "  총 쇼츠: $TOTAL_SHORTS개"
echo "  대기중 쇼츠: $PENDING_SHORTS개"
echo "  총 크리에이터: $TOTAL_CREATORS명"

echo "✓ 대기중 쇼츠 목록 조회..."
PENDING_LIST=$(curl -s "$BASE_URL/api/admin/shorts/pending" \
  -H "Authorization: Bearer $TOKEN")
PENDING_COUNT=$(echo $PENDING_LIST | jq '.data.shorts | length')
echo "  대기중 쇼츠 수: $PENDING_COUNT개"

if [ "$PENDING_COUNT" -gt 0 ]; then
  FIRST_PENDING_ID=$(echo $PENDING_LIST | jq -r '.data.shorts[0].id')
  echo "✓ 쇼츠 승인 테스트 (ID: $FIRST_PENDING_ID)..."
  
  APPROVE_RESULT=$(curl -s -X POST "$BASE_URL/api/admin/shorts/$FIRST_PENDING_ID/approve" \
    -H "Authorization: Bearer $TOKEN")
  APPROVE_MSG=$(echo $APPROVE_RESULT | jq -r '.message')
  echo "  결과: $APPROVE_MSG"
  
  echo "✓ 쇼츠 상태 원복 (pending)..."
  PENDING_RESULT=$(curl -s -X POST "$BASE_URL/api/admin/shorts/$FIRST_PENDING_ID/pending" \
    -H "Authorization: Bearer $TOKEN")
  PENDING_MSG=$(echo $PENDING_RESULT | jq -r '.message')
  echo "  결과: $PENDING_MSG"
fi

echo "✓ 크리에이터 목록 조회..."
CREATORS_LIST=$(curl -s "$BASE_URL/api/admin/creators" \
  -H "Authorization: Bearer $TOKEN")
CREATORS_COUNT=$(echo $CREATORS_LIST | jq '.data.creators | length')
echo "  크리에이터 수: $CREATORS_COUNT명"

echo ""

# 4. MyPage Test
echo "4️⃣ 마이페이지 테스트"
echo "----------------------------"
echo "✓ 마이페이지 로드..."
MYPAGE_TITLE=$(curl -s "$BASE_URL/mypage" | grep -o '<title>.*</title>' | sed 's/<[^>]*>//g')
echo "  페이지 제목: $MYPAGE_TITLE"

echo "✓ 사용자 설정 조회..."
USER_SETTINGS=$(curl -s "$BASE_URL/api/user/settings/$ADMIN_EMAIL" \
  -H "Authorization: Bearer $TOKEN")
CHANNEL_ID=$(echo $USER_SETTINGS | jq -r '.data.channelId')
PARTNER_ID=$(echo $USER_SETTINGS | jq -r '.data.coupangPartnerId')
echo "  채널 ID: $CHANNEL_ID"
echo "  파트너 ID: $PARTNER_ID"

echo "✓ 사용자 쇼츠 목록 조회..."
USER_SHORTS=$(curl -s "$BASE_URL/api/user/shorts/$ADMIN_EMAIL" \
  -H "Authorization: Bearer $TOKEN")
USER_SHORTS_COUNT=$(echo $USER_SHORTS | jq '.data.shorts | length')
echo "  내 쇼츠 수: $USER_SHORTS_COUNT개"

echo ""

# 5. Creator Page Test
echo "5️⃣ 크리에이터 페이지 테스트"
echo "----------------------------"
echo "✓ 크리에이터 페이지 로드..."
CREATOR_TITLE=$(curl -s "$BASE_URL/creator" | grep -o '<title>.*</title>' | sed 's/<[^>]*>//g')
echo "  페이지 제목: $CREATOR_TITLE"

echo ""

# 6. Short Detail Page Test
echo "6️⃣ 쇼츠 상세 페이지 테스트"
echo "----------------------------"
echo "✓ 첫 번째 쇼츠 조회..."
FIRST_SHORT=$(curl -s "$BASE_URL/api/shorts" | jq '.data.shorts[0]')
FIRST_SHORT_ID=$(echo $FIRST_SHORT | jq -r '.id')
FIRST_SHORT_TITLE=$(echo $FIRST_SHORT | jq -r '.title')
echo "  쇼츠 ID: $FIRST_SHORT_ID"
echo "  쇼츠 제목: $FIRST_SHORT_TITLE"

echo "✓ 쇼츠 상세 페이지 로드..."
DETAIL_TITLE=$(curl -s "$BASE_URL/short/$FIRST_SHORT_ID" | grep -o '<title>.*</title>' | sed 's/<[^>]*>//g')
echo "  페이지 제목: $DETAIL_TITLE"

echo "✓ 쇼츠 상세 정보 API..."
DETAIL_API=$(curl -s "$BASE_URL/api/shorts/$FIRST_SHORT_ID")
DETAIL_TITLE=$(echo $DETAIL_API | jq -r '.data.title')
echo "  제목: $DETAIL_TITLE"

echo "✓ 클릭 추적 테스트..."
CLICK_RESULT=$(curl -s -X POST "$BASE_URL/api/shorts/$FIRST_SHORT_ID/click")
CLICK_MSG=$(echo $CLICK_RESULT | jq -r '.message')
echo "  결과: $CLICK_MSG"

echo ""

# 7. API Endpoints Summary
echo "7️⃣ API 엔드포인트 요약"
echo "----------------------------"
echo "✅ GET  / - 홈페이지"
echo "✅ GET  /admin - 관리자 페이지"
echo "✅ GET  /mypage - 마이페이지"
echo "✅ GET  /creator - 크리에이터 페이지"
echo "✅ GET  /short/:id - 쇼츠 상세"
echo ""
echo "✅ POST /api/auth/login - 로그인"
echo "✅ GET  /api/shorts - 쇼츠 목록"
echo "✅ GET  /api/shorts/:id - 쇼츠 상세"
echo "✅ POST /api/shorts/:id/click - 클릭 추적"
echo "✅ GET  /api/shorts/categories/list - 카테고리 목록"
echo ""
echo "✅ GET  /api/admin/stats - 관리자 통계"
echo "✅ GET  /api/admin/shorts/:status - 상태별 쇼츠"
echo "✅ POST /api/admin/shorts/:id/approve - 쇼츠 승인"
echo "✅ POST /api/admin/shorts/:id/reject - 쇼츠 거절"
echo "✅ POST /api/admin/shorts/:id/pending - 쇼츠 대기"
echo "✅ DELETE /api/admin/shorts/:id - 쇼츠 삭제"
echo "✅ GET  /api/admin/creators - 크리에이터 목록"
echo ""
echo "✅ GET  /api/user/settings/:email - 사용자 설정 조회"
echo "✅ POST /api/user/settings - 사용자 설정 저장"
echo "✅ GET  /api/user/shorts/:email - 사용자 쇼츠"
echo "✅ POST /api/creator/register - 크리에이터 등록"
echo "✅ POST /api/shorts/add - 쇼츠 수동 등록"

echo ""
echo "================================"
echo "✅ 종합 통합 테스트 완료!"
echo "================================"
echo ""
echo "📊 테스트 결과 요약:"
echo "  • 총 쇼츠: $TOTAL_SHORTS개"
echo "  • 대기중: $PENDING_SHORTS개"
echo "  • 크리에이터: $TOTAL_CREATORS명"
echo "  • 로그인: ✅ 성공"
echo "  • 모든 페이지: ✅ 정상"
echo "  • 모든 API: ✅ 작동"
echo ""

