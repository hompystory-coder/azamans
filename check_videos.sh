#!/bin/bash
# Quick video generation status checker

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🎬 Quick Video Status Check"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Latest video
LATEST_JOB=$(cat /var/www/mfx.neuralgrid.kr/shorts_history.json | jq -r '.[] | select(.status == "completed") | "\(.jobId)|\(.characterId)|\(.videoUrl)|\(.createdAt)"' | tail -1)

if [ ! -z "$LATEST_JOB" ]; then
    JOB_ID=$(echo "$LATEST_JOB" | cut -d'|' -f1)
    CHAR_ID=$(echo "$LATEST_JOB" | cut -d'|' -f2)
    VIDEO_URL=$(echo "$LATEST_JOB" | cut -d'|' -f3)
    CREATED=$(echo "$LATEST_JOB" | cut -d'|' -f4)
    
    echo "✅ Latest Successful Video:"
    echo "   Job: $JOB_ID"
    echo "   Character: $CHAR_ID"
    echo "   Created: $CREATED"
    echo "   URL: https://mfx.neuralgrid.kr$VIDEO_URL"
    echo ""
    
    # Check file
    FILE_PATH="/var/www/mfx.neuralgrid.kr/public$VIDEO_URL"
    if [ -f "$FILE_PATH" ]; then
        SIZE=$(ls -lh "$FILE_PATH" | awk '{print $5}')
        echo "   File: ✅ EXISTS ($SIZE)"
    else
        echo "   File: ❌ NOT FOUND"
    fi
else
    echo "❌ No completed videos found"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 Statistics:"

TOTAL=$(ls -1 /var/www/mfx.neuralgrid.kr/public/videos/*.mp4 2>/dev/null | wc -l)
SIZE=$(du -sh /var/www/mfx.neuralgrid.kr/public/videos/ 2>/dev/null | awk '{print $1}')
RECENT=$(cat /var/www/mfx.neuralgrid.kr/shorts_history.json | jq '[.[] | select(.createdAt > "2025-12-24T00:00:00Z" and .status == "completed")] | length')

echo "   Total Videos: $TOTAL"
echo "   Total Size: $SIZE"
echo "   Today: $RECENT completed"
echo ""

echo "🔗 Quick Links:"
echo "   Generate: https://mfx.neuralgrid.kr/character"
echo "   Preview: https://shorts.neuralgrid.kr/preview"
echo ""
