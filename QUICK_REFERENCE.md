# 🎬 AI 쇼츠 생성 시스템 - 빠른 참조

## 📺 접속 URL

| 페이지 | URL | 설명 |
|--------|-----|------|
| 🎭 캐릭터 선택 | https://mfx.neuralgrid.kr/character | 39개 프리미엄 캐릭터 선택 |
| 📝 크롤러 | https://mfx.neuralgrid.kr/crawler | 제품 URL 입력 |
| 🎬 생성 | https://mfx.neuralgrid.kr/generation | 영상 자동 생성 또는 최근 영상 보기 |
| 📺 미리보기 | https://shorts.neuralgrid.kr/preview | 77+ 영상, 46개 필터 |
| 📜 히스토리 | https://mfx.neuralgrid.kr/history | 생성 기록 확인 |

---

## ✨ 새 영상 생성 (3단계)

```
1. /character → 캐릭터 선택 (39개)
2. /crawler   → 제품 URL 입력
3. /generation → 자동 생성 (10-15분)
```

---

## 🎨 39개 프리미엄 캐릭터

### 💼 비즈니스 (5개)
executive-fox, ceo-lion, strategist-eagle, negotiator-wolf, consultant-owl

### 🚀 테크 (5개)
tech-fox, dev-raccoon, ai-panda, startup-tiger, blockchain-monkey

### 👗 패션 (5개)
fashionista-cat, stylist-peacock, luxury-leopard, trendy-rabbit, designer-swan

### ⚽ 스포츠 (5개)
athlete-cheetah, trainer-bear, yoga-deer, runner-kangaroo, fighter-dragon

### 🍜 푸드 (5개)
chef-penguin, foodie-hamster, barista-otter, sommelier-fox, baker-bear

### 🎪 엔터테인먼트 (5개)
comedian-parrot, musician-fox, dancer-peacock, artist-cat, gamer-otter

### 🐾 레거시 (9개)
clever-fox, happy-rabbit, wise-owl, energetic-dog, calm-cat, cheerful-bear, creative-penguin, brave-lion, adventurous-monkey

---

## 🛠️ 유용한 명령어

```bash
# 상태 확인
/home/azamans/webapp/check_videos.sh

# 멈춘 작업 정리
python3 /var/www/mfx.neuralgrid.kr/scripts/fix_stuck_jobs.py

# 서비스 재시작
cd /var/www/mfx.neuralgrid.kr && pm2 restart mfx-shorts

# 최신 영상 확인
ls -lth /var/www/mfx.neuralgrid.kr/public/videos/*.mp4 | head -5
```

---

## 📊 시스템 상태

- **총 영상**: 51개
- **프리미엄 캐릭터**: 39개
- **서비스 상태**: ✅ 모두 실행 중
- **최근 업데이트**: 2025-12-24

---

## 🆘 문제 해결

### "영상이 안 보여요"
→ /generation 페이지로 이동 (최근 영상 자동 표시)

### "새 영상을 만들고 싶어요"
→ /character 페이지에서 시작

### "멈춘 작업이 있어요"
→ `python3 /var/www/mfx.neuralgrid.kr/scripts/fix_stuck_jobs.py`

---

**업데이트**: 2025-12-24  
**문서**: /home/azamans/webapp/
