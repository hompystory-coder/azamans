#!/bin/bash
#
# 차단된 IP 목록 및 통계 확인
#

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🔒 SSH 차단 IP 현황"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 현재 차단된 IP 수
TOTAL_BLOCKED=$(iptables -L INPUT -n | grep -c "DROP")
echo "📊 현재 차단 중인 IP: ${TOTAL_BLOCKED}개"
echo ""

# 차단된 IP 목록
if [ "$TOTAL_BLOCKED" -gt 0 ]; then
    echo "🚫 차단된 IP 목록:"
    iptables -L INPUT -n --line-numbers | grep "DROP" | awk '{printf "   %3s. %s\n", $1, $4}'
    echo ""
fi

# 차단 기록 파일 확인
BLOCK_LOG="/var/log/ssh_blocked_ips.log"
if [ -f "$BLOCK_LOG" ]; then
    echo "📝 최근 차단 기록 (최근 10개):"
    tail -10 "$BLOCK_LOG" | awk '{print "   " $0}'
    echo ""
    echo "📂 전체 기록: $BLOCK_LOG"
else
    echo "⚠️  차단 기록 파일이 없습니다."
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "💡 관리 명령어:"
echo "   - 차단 스크립트 실행: sudo /home/mvc/ssh_fail_block.sh"
echo "   - IP 차단 해제: sudo /home/mvc/ssh_unblock_ip.sh <IP주소>"
echo "   - 전체 차단 해제: sudo iptables -F INPUT"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
