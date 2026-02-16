#!/bin/bash
#
# SSH 로그인 실패 5회 이상 시 iptables로 IP 차단
# CentOS 7/8 호환
#

# 설정
THRESHOLD=5                      # 차단 기준 실패 횟수
LOG_FILE="/var/log/secure"       # SSH 로그 파일
BLOCK_LIST="/var/log/ssh_blocked_ips.log"  # 차단 IP 기록 파일
TEMP_FILE="/tmp/ssh_fail_ips.tmp"

# 로그 파일 확인
if [ ! -f "$LOG_FILE" ]; then
    echo "❌ 오류: $LOG_FILE 파일이 없습니다."
    exit 1
fi

# iptables 설치 확인
if ! command -v iptables &> /dev/null; then
    echo "❌ iptables가 설치되지 않았습니다."
    exit 1
fi

echo "🔍 SSH 로그인 실패 IP 분석 중..."
echo "📊 차단 기준: ${THRESHOLD}회 이상 실패"
echo ""

# SSH 로그인 실패 IP 추출 및 카운트
# "Failed password" 또는 "Invalid user" 패턴 검색
grep -E "Failed password|Invalid user" "$LOG_FILE" | \
    grep -oE "([0-9]{1,3}\.){3}[0-9]{1,3}" | \
    sort | uniq -c | sort -rn > "$TEMP_FILE"

# 차단 대상 IP 처리
BLOCKED_COUNT=0

while read count ip; do
    # 이미 차단된 IP인지 확인
    if iptables -L INPUT -n | grep -q "$ip"; then
        echo "⏭️  $ip (실패: ${count}회) - 이미 차단됨"
        continue
    fi
    
    # 차단 기준 초과 시 iptables에 추가
    if [ "$count" -ge "$THRESHOLD" ]; then
        echo "🚫 차단: $ip (실패: ${count}회)"
        
        # iptables DROP 규칙 추가
        iptables -I INPUT -s "$ip" -j DROP
        
        # 차단 기록
        echo "$(date '+%Y-%m-%d %H:%M:%S') | $ip | 실패 횟수: ${count}" >> "$BLOCK_LIST"
        
        BLOCKED_COUNT=$((BLOCKED_COUNT + 1))
    else
        echo "✅ $ip (실패: ${count}회) - 기준 미달"
    fi
done < "$TEMP_FILE"

# 결과 출력
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📋 처리 완료"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🚫 새로 차단된 IP: ${BLOCKED_COUNT}개"
echo "📝 차단 기록: $BLOCK_LIST"
echo ""

# 현재 차단된 IP 목록 표시
TOTAL_BLOCKED=$(iptables -L INPUT -n | grep -c "DROP")
echo "🔒 현재 차단 중인 IP 총 ${TOTAL_BLOCKED}개:"
iptables -L INPUT -n | grep "DROP" | awk '{print "   - " $4}' | head -10

if [ "$TOTAL_BLOCKED" -gt 10 ]; then
    echo "   ... (${TOTAL_BLOCKED}개 중 10개만 표시)"
fi

# 임시 파일 삭제
rm -f "$TEMP_FILE"

echo ""
echo "💡 차단 해제 방법:"
echo "   iptables -D INPUT -s <IP주소> -j DROP"
echo ""
echo "💡 전체 차단 규칙 확인:"
echo "   iptables -L INPUT -n --line-numbers"
