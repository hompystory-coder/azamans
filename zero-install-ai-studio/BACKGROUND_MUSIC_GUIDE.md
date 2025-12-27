# 🎵 배경음악 통합 시스템 가이드

## 📋 목차
1. [시스템 개요](#시스템-개요)
2. [프로세스 흐름](#프로세스-흐름)
3. [기술 구현](#기술-구현)
4. [오디오 믹싱](#오디오-믹싱)
5. [사용 예시](#사용-예시)
6. [문제 해결](#문제-해결)

---

## 시스템 개요

### 🎯 목적
AI 쇼츠 비디오에 스토리 분위기에 맞는 배경음악을 자동으로 추가하여 완성도를 높입니다.

### ✨ 주요 기능
- **자동 배경음악 매칭**: 스토리의 장르와 분위기에 따라 최적의 배경음악 선택
- **나레이션 + BGM 믹싱**: 나레이션이 묻히지 않도록 배경음악 볼륨 자동 조절
- **스마트 오디오 처리**: 비디오 길이에 맞춰 배경음악 자동 반복/트리밍
- **페이드 효과**: 부드러운 시작과 끝을 위한 페이드인/아웃 적용

---

## 프로세스 흐름

### 📊 전체 파이프라인
```
1. 스토리 생성 (장르, 분위기 포함)
   ↓
2. 씬별 이미지 생성 (7개 씬)
   ↓
3. 씬별 TTS 나레이션 생성
   ↓
4. 카메라 효과 적용
   ↓
5.5. 🆕 배경음악 매칭 ← 비디오 생성 전에 수행!
   ↓
5. 비디오 합성 (이미지 + 카메라 효과 + 나레이션 + BGM)
   ↓
6. 완성된 AI 쇼츠 출력
```

### 🎼 배경음악 매칭 단계 (5.5단계)
```javascript
// 프론트엔드: app/pro-shorts/page.tsx

// 5.5단계: 배경음악 매칭 (비디오 생성 전)
let backgroundMusicUrl: string | null = null;

const musicResponse = await fetch('/api/music', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    mood: generatedStory.mood,      // 예: "미래적"
    genre: generatedStory.genre,    // 예: "SF"
    title: generatedStory.title     // 예: "우주 비행사의 모험"
  })
});

if (musicResponse.ok) {
  const musicData = await musicResponse.json();
  backgroundMusicUrl = musicData.music.url;
  // 예: "https://cdn.pixabay.com/audio/2022/03/30/audio_d0fa0fc621.mp3"
}

// 5단계: 비디오 생성 (배경음악 포함)
const videoResponse = await fetch('/api/video', {
  method: 'POST',
  body: JSON.stringify({
    title: generatedStory.title,
    scenes: [...],
    background_music_url: backgroundMusicUrl, // 🆕 배경음악 전달
    fps: 30
  })
});
```

---

## 기술 구현

### 🔧 백엔드 구조 (video_generator.py)

#### 1. 배경음악 파라미터 수신
```python
@app.route('/generate-video', methods=['POST'])
def generate_video():
    data = request.json
    background_music_url = data.get('background_music_url', None)  # 🆕
    
    success = create_video_from_images(
        processed_scenes, 
        output_path, 
        fps, 
        background_music_url=background_music_url  # 전달
    )
```

#### 2. 배경음악 다운로드/로드
```python
def create_video_from_images(images_data, output_path, fps=30, background_music_url=None):
    # ... 비디오 클립 생성 ...
    
    if background_music_url:
        bgm_path = None
        
        if background_music_url.startswith('http'):
            # URL인 경우 다운로드
            response = requests.get(background_music_url, timeout=10)
            if response.status_code == 200:
                timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
                bgm_path = f'/tmp/bgm_{timestamp}.mp3'
                with open(bgm_path, 'wb') as f:
                    f.write(response.content)
        
        elif background_music_url.startswith('/'):
            # 로컬 파일 경로
            bgm_path = background_music_url
```

#### 3. 배경음악 길이 조정
```python
if bgm_path and os.path.exists(bgm_path):
    bgm_clip = AudioFileClip(bgm_path)
    video_duration = final_clip.duration
    
    if bgm_clip.duration < video_duration:
        # 배경음악이 짧으면 반복
        num_loops = int(video_duration / bgm_clip.duration) + 1
        bgm_clip = bgm_clip.loop(n=num_loops).subclipped(0, video_duration)
    
    elif bgm_clip.duration > video_duration:
        # 배경음악이 길면 자르기
        bgm_clip = bgm_clip.subclipped(0, video_duration)
```

#### 4. 페이드 효과 및 볼륨 조절
```python
# 페이드인/아웃 효과 (각 1초)
bgm_clip = bgm_clip.with_effects([
    ("audio_fadein", 1.0),
    ("audio_fadeout", 1.0)
])

# 볼륨 30%로 낮춤 (나레이션이 주가 되도록)
bgm_clip = bgm_clip.multiply_volume(0.3)
```

#### 5. 나레이션 + BGM 믹싱
```python
from moviepy import CompositeAudioClip

if final_clip.audio is not None:
    # 나레이션 + 배경음악 믹싱
    mixed_audio = CompositeAudioClip([final_clip.audio, bgm_clip])
    final_clip = final_clip.with_audio(mixed_audio)
else:
    # 나레이션이 없으면 배경음악만
    final_clip = final_clip.with_audio(bgm_clip)
```

---

## 오디오 믹싱

### 🎚️ 볼륨 밸런스

#### 현재 설정
- **나레이션**: 100% (원본 볼륨)
- **배경음악**: 30% (multiply_volume(0.3))

#### 이유
나레이션이 스토리의 핵심 정보를 전달하므로, 배경음악은 분위기 연출에만 집중합니다.

### 🎵 오디오 처리 케이스

| 케이스 | 나레이션 | 배경음악 | 최종 출력 |
|--------|---------|---------|-----------|
| 1 | ✅ 있음 | ✅ 있음 | 나레이션 100% + BGM 30% 믹싱 |
| 2 | ✅ 있음 | ❌ 없음 | 나레이션만 (100%) |
| 3 | ❌ 없음 | ✅ 있음 | 배경음악만 (30%) |
| 4 | ❌ 없음 | ❌ 없음 | 무음 비디오 |

### 🔊 페이드 효과 타임라인
```
시작 0초     1초                     비디오 끝-1초   비디오 끝
  |-----------|----------------------------|-----------|
  페이드인 →   풀 볼륨 (30%)               ← 페이드아웃
  0% → 30%                                 30% → 0%
```

---

## 사용 예시

### 예시 1: SF 우주 스토리
```json
{
  "story": {
    "title": "우주 비행사의 모험",
    "genre": "SF",
    "mood": "미래적",
    "total_duration": 30
  },
  "backgroundMusic": {
    "name": "Epic Cinematic",
    "url": "https://cdn.pixabay.com/audio/2022/03/30/audio_d0fa0fc621.mp3",
    "description": "웅장하고 영화적인 배경음악"
  }
}
```

**결과**: 웅장한 오케스트라 배경음악이 우주 모험 나레이션과 함께 재생됩니다.

### 예시 2: 로맨틱 스토리
```json
{
  "story": {
    "title": "별빛 아래의 만남",
    "genre": "로맨스",
    "mood": "설렘",
    "total_duration": 30
  },
  "backgroundMusic": {
    "name": "Romantic Piano",
    "url": "https://cdn.pixabay.com/audio/2022/05/27/audio_1808fbf07a.mp3",
    "description": "감성적인 피아노 연주"
  }
}
```

**결과**: 감성적인 피아노 배경음악이 로맨틱한 나레이션과 조화롭게 믹싱됩니다.

### 예시 3: 전통 동화
```json
{
  "story": {
    "title": "선녀와 나무꾼",
    "genre": "동화",
    "mood": "전통적",
    "total_duration": 30
  },
  "backgroundMusic": {
    "name": "Korean Traditional",
    "url": "https://cdn.pixabay.com/audio/2021/09/17/audio_7181e8a4f0.mp3",
    "description": "한국 전통 음악"
  }
}
```

**결과**: 한국 전통 음악이 동화 나레이션과 함께 재생되어 몰입감을 높입니다.

---

## 문제 해결

### ❌ 배경음악이 적용되지 않음

#### 증상
비디오에 나레이션만 들리고 배경음악이 없습니다.

#### 해결 방법
1. **프론트엔드 확인**: 배경음악 매칭이 비디오 생성 전에 수행되는지 확인
   ```javascript
   // ✅ 올바른 순서
   // 5.5단계: 배경음악 매칭
   const musicResponse = await fetch('/api/music', {...});
   backgroundMusicUrl = musicData.music.url;
   
   // 5단계: 비디오 생성 (배경음악 포함)
   const videoResponse = await fetch('/api/video', {
     body: JSON.stringify({
       background_music_url: backgroundMusicUrl  // 전달 확인
     })
   });
   ```

2. **백엔드 로그 확인**:
   ```bash
   pm2 logs ai-video-generator --lines 50 | grep -i "background\|bgm\|music"
   ```
   
   정상 로그:
   ```
   INFO: Adding background music: https://cdn.pixabay.com/audio/...
   INFO:   → Downloaded BGM to: /tmp/bgm_20251227_143518.mp3
   INFO:   → Looping BGM (original: 120.0s, needed: 30.0s)
   INFO:   → Mixing narration + BGM
   INFO:   ✅ Background music added successfully!
   ```

3. **배경음악 URL 유효성 확인**:
   ```bash
   # URL 다운로드 테스트
   curl -I "https://cdn.pixabay.com/audio/2022/03/30/audio_d0fa0fc621.mp3"
   # HTTP/1.1 200 OK 확인
   ```

### ❌ 배경음악이 나레이션을 묻어버림

#### 증상
배경음악 소리가 너무 커서 나레이션이 잘 안 들립니다.

#### 해결 방법
1. **볼륨 조절**: `video_generator.py` 수정
   ```python
   # 현재: 30%
   bgm_clip = bgm_clip.multiply_volume(0.3)
   
   # 더 낮추려면: 20%
   bgm_clip = bgm_clip.multiply_volume(0.2)
   ```

2. **서버 재시작**:
   ```bash
   cd /home/azamans/webapp/zero-install-ai-studio
   pm2 restart ai-video-generator
   ```

### ❌ 배경음악이 반복되지 않고 중간에 끊김

#### 증상
30초 비디오인데 배경음악이 10초만 재생되고 끊깁니다.

#### 해결 방법
1. **반복 로직 확인**: `video_generator.py`
   ```python
   if bgm_clip.duration < video_duration:
       # 반복 횟수 계산
       num_loops = int(video_duration / bgm_clip.duration) + 1
       bgm_clip = bgm_clip.loop(n=num_loops).subclipped(0, video_duration)
   ```

2. **로그 확인**:
   ```bash
   pm2 logs ai-video-generator | grep "Looping BGM"
   # INFO: → Looping BGM (original: 10.0s, needed: 30.0s)
   ```

### ❌ MoviePy CompositeAudioClip 오류

#### 증상
```
ModuleNotFoundError: No module named 'moviepy'
```

#### 해결 방법
```bash
cd /home/azamans/webapp/zero-install-ai-studio/ai-backend
pip install moviepy
pm2 restart ai-video-generator
```

---

## 성능 최적화

### ⚡ 배경음악 다운로드 최적화
- 외부 URL에서 다운로드 시 최대 10초 타임아웃 설정
- 다운로드 실패 시 배경음악 없이 계속 진행
- `/tmp` 디렉토리에 임시 저장 (자동 정리)

### 🎬 비디오 생성 시간
| 구성 요소 | 처리 시간 |
|----------|----------|
| 씬별 이미지 로드 | ~1초 (7개) |
| 나레이션 오디오 첨부 | ~2초 (7개) |
| 카메라 효과 적용 | ~3초 (실시간) |
| 배경음악 다운로드 | ~1초 |
| 배경음악 처리 (반복/트림) | ~1초 |
| 오디오 믹싱 | ~2초 |
| 비디오 인코딩 | ~20초 (30초 영상) |
| **총 처리 시간** | **~30초** |

---

## 테스트 체크리스트

### ✅ 기능 테스트
- [ ] 배경음악이 비디오에 포함되는가?
- [ ] 나레이션과 배경음악이 동시에 들리는가?
- [ ] 배경음악 볼륨이 적절한가? (나레이션이 잘 들림)
- [ ] 배경음악 페이드인/아웃이 부드러운가?
- [ ] 배경음악이 비디오 길이에 맞게 반복/트림되는가?

### ✅ 장르별 테스트
- [ ] SF 스토리 → Epic Cinematic 배경음악
- [ ] 로맨스 스토리 → Romantic Piano 배경음악
- [ ] 동화 스토리 → Korean Traditional 배경음악
- [ ] 공포 스토리 → Mysterious Ambient 배경음악
- [ ] 코미디 스토리 → Joyful Acoustic 배경음악

### ✅ 엣지 케이스 테스트
- [ ] 배경음악 없이 비디오 생성 (나레이션만)
- [ ] 나레이션 없이 비디오 생성 (배경음악만)
- [ ] 배경음악 URL 다운로드 실패 시 폴백
- [ ] 매우 짧은 배경음악 (5초) → 30초 비디오 반복
- [ ] 매우 긴 배경음악 (180초) → 30초 비디오 트림

---

## 다음 단계

### 🚀 향후 개선 사항
1. **배경음악 볼륨 자동 조절**
   - 나레이션 음량에 따라 배경음악 볼륨 자동 조절
   - 나레이션이 없는 구간에서는 배경음악 볼륨 높이기

2. **배경음악 라이브러리 확장**
   - 현재 7개 → 50개 이상으로 확장
   - 장르/분위기별 다양한 선택지 제공

3. **사용자 배경음악 업로드**
   - 사용자가 직접 배경음악 업로드
   - 커스텀 배경음악 적용

4. **배경음악 프리뷰**
   - 비디오 생성 전 배경음악 미리 듣기
   - 다른 배경음악으로 교체 가능

---

## 참고 자료

### 📚 관련 문서
- [카메라 효과 가이드](CAMERA_EFFECTS_GUIDE.md)
- [프론트엔드-백엔드 통합 가이드](FRONTEND_BACKEND_INTEGRATION.md)
- [전체 시스템 가이드](END_TO_END_GUIDE.md)

### 🔗 외부 리소스
- [MoviePy 공식 문서](https://zulko.github.io/moviepy/)
- [Pixabay 무료 음악](https://pixabay.com/music/)
- [CC0 라이선스](https://creativecommons.org/publicdomain/zero/1.0/)

### 🎵 배경음악 출처
모든 배경음악은 Pixabay에서 제공하는 CC0 라이선스 음악입니다.
- 상업적 사용 가능
- 저작권 표시 불필요
- 무료 사용 가능

---

## 마무리

### 🎉 완성된 기능
✅ 배경음악 자동 매칭  
✅ 나레이션 + BGM 믹싱  
✅ 스마트 오디오 처리  
✅ 페이드 효과  
✅ 장르별 배경음악  

### 📍 현재 상태
**모든 AI 쇼츠에 배경음악이 자동으로 추가됩니다!**

접속: **https://ai-studio.neuralgrid.kr/pro-shorts**

---

**문서 작성일**: 2024-12-27  
**마지막 업데이트**: 2024-12-27  
**버전**: 1.0.0
