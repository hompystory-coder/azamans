#!/bin/bash
#
# iptables에서 특정 IP 차단 해제
#

if [ -z "$1" ]; then
    echo "사용법: $0 <IP주소>"
    echo ""
    echo "예시: $0 192.168.1.100"
    echo ""
    echo "현재 차단된 IP 목록:"
    iptables -L INPUT -n --line-numbers | grep "DROP"
    exit 1
fi

IP=$1

# IP 형식 검증
if ! [[ $IP =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]]; then
    echo "❌ 오류: 올바른 IP 주소 형식이 아닙니다."
    exit 1
fi

# 차단 해제
if iptables -L INPUT -n | grep -q "$IP"; then
    iptables -D INPUT -s "$IP" -j DROP
    echo "✅ $IP 차단이 해제되었습니다."
else
    echo "⚠️  $IP는 차단되지 않은 IP입니다."
fi

echo ""
echo "현재 차단 중인 IP:"
iptables -L INPUT -n | grep "DROP" | awk '{print "   - " $4}'
