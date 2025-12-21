const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs').promises;
const { exec } = require('child_process');
const { promisify } = require('util');
const execAsync = promisify(exec);

// 파일 업로드 설정
const storage = multer.diskStorage({
  destination: async (req, file, cb) => {
    const uploadDir = '/mnt/music-storage/shorts-videos/uploads';
    await fs.mkdir(uploadDir, { recursive: true });
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
  }
});

const upload = multer({ 
  storage: storage,
  limits: { fileSize: 50 * 1024 * 1024 } // 50MB
});

// 작업 상태 저장소
const jobs = new Map();

// POST /api/advanced/upload-bgm - 배경 음악 업로드
router.post('/upload-bgm', upload.array('bgm', 10), async (req, res) => {
  try {
    const files = req.files.map(file => ({
      id: path.basename(file.filename, path.extname(file.filename)),
      name: file.originalname,
      path: file.path,
      url: `/uploads/${file.filename}`
    }));

    res.json({
      success: true,
      files
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// POST /api/advanced/upload-background - 배경 이미지 업로드
router.post('/upload-background', upload.array('background', 10), async (req, res) => {
  try {
    const files = req.files.map(file => ({
      id: path.basename(file.filename, path.extname(file.filename)),
      name: file.originalname,
      path: file.path,
      url: `/uploads/${file.filename}`
    }));

    res.json({
      success: true,
      files
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// GET /api/advanced/voice/sample/:type - 음성 샘플 생성 (16가지 음성)
router.get('/voice/sample/:type', async (req, res) => {
  try {
    const { type } = req.params;
    const AUDIO_DIR = '/mnt/music-storage/shorts-videos/audio';
    await fs.mkdir(AUDIO_DIR, { recursive: true });

    // 16가지 음성 샘플 텍스트
    const sampleText = {
      // 여성 음성 - 일반
      'female-friendly': '안녕하세요! 저는 친근한 여성 음성이에요. 이 음성으로 쇼츠를 만들어드릴게요!',
      'female-energetic': '안녕! 나는 발랄한 여성 음성이야! 재미있고 활기찬 쇼츠를 만들어줄게!',
      'female-professional': '안녕하십니까. 전문적인 여성 음성입니다. 신뢰감 있는 내용을 전달하겠습니다.',
      'female-soft': '안녕하세요... 부드러운 여성 음성이에요. 편안하게 들려드릴게요.',
      'female-mature': '안녕하세요. 성숙한 여성 음성입니다. 깊이 있는 내용을 전달해드립니다.',
      'female-young': '안녕하세용! 귀여운 여성 음성이에용! 즐거운 쇼츠를 만들어요!',
      
      // 여성 음성 - 특수
      'female-news': '안녕하십니까. 뉴스 앵커 여성 음성입니다. 명확하고 권위 있게 전달하겠습니다.',
      'female-drama': '안녕하세요! 드라마틱한 여성 음성이에요! 감동적인 이야기를 들려드릴게요!',
      'female-whisper': '안녕하세요... 속삭이는 여성 음성이에요... 편안하고 조용하게 전달할게요...',
      
      // 남성 음성 - 일반
      'male-professional': '안녕하십니까. 전문적인 남성 음성입니다. 신뢰감 있는 내레이션을 제공합니다.',
      'male-calm': '안녕하세요. 차분한 남성 음성입니다. 편안하고 안정적인 분위기를 만들어드립니다.',
      'male-energetic': '안녕하세요! 활기찬 남성 음성입니다! 에너지 넘치는 쇼츠를 만들어드리겠습니다!',
      'male-deep': '안녕하십니까. 중저음 남성 음성입니다. 깊이 있고 묵직한 느낌을 전달합니다.',
      'male-young': '안녕하세요! 젊은 남성 음성입니다! 트렌디하고 활발한 내용을 전달해요!',
      'male-narration': '안녕하십니까. 내레이션 전문 남성 음성입니다. 명확하고 정확한 전달을 약속드립니다.',
      
      // 남성 음성 - 특수
      'male-powerful': '안녕하십니까! 파워풀한 남성 음성입니다! 강렬하고 힘있게 전달하겠습니다!',
      'male-documentary': '안녕하십니까. 다큐멘터리 남성 음성입니다. 깊이 있고 지적인 내용을 전달합니다.',
      
      // 특수 음성
      'child-friendly': '안녕! 친근한 아동용 음성이야! 재미있게 놀면서 배워보자!',
      'elderly-wise': '안녕하시오. 어르신의 지혜로운 음성이오. 경험에서 우러나온 말씀을 전하겠소.',
      'robot-ai': '안녕하세요. 저는 AI 로봇 음성입니다. 정확하고 논리적인 정보를 전달합니다.',
      'asmr-relaxing': '안녕하세요... 편안한 ASMR 힐링 음성이에요... 차분하고 평온하게 들려드릴게요...'
    };

    const text = sampleText[type] || sampleText['female-friendly'];
    const audioPath = path.join(AUDIO_DIR, `sample_${type}.mp3`);

    // 캐시된 샘플이 있는지 확인
    try {
      await fs.access(audioPath);
      return res.sendFile(audioPath);
    } catch {
      // 없으면 생성
      console.log(`Generating voice sample for: ${type}`);
      await execAsync(`gtts-cli "${text}" --lang ko --output "${audioPath}"`);
      res.sendFile(audioPath);
    }

  } catch (error) {
    console.error('Voice sample generation error:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// POST /api/advanced/generate-script - AI 스크립트 생성
router.post('/generate-script', async (req, res) => {
  try {
    const { content, prompt, character, mode } = req.body;

    // OpenAI API 호출하여 스크립트 생성
    const axios = require('axios');
    
    const systemPrompt = prompt || `블로그 내용을 바탕으로 30초 분량의 매력적인 쇼츠 스크립트를 작성해주세요.
- 5개의 장면으로 구성
- 각 장면은 6초 분량
- 친근하고 매력적인 톤
- 핵심 정보를 명확하게 전달
- 마지막에 행동 유도 문구 포함`;

    const userContent = `
제목: ${content.title || ''}
내용: ${content.content || content.text || ''}
이미지: ${(content.images || []).length}개

위 내용을 바탕으로 ${mode === 'character' ? '캐릭터' : mode === 'realistic' ? '실사' : '혼합'} 스타일의 쇼츠 스크립트를 JSON 형식으로 작성해주세요.

응답 형식:
{
  "scenes": [
    {
      "text": "장면 대사",
      "duration": 6,
      "imageIndex": 0 (사용할 이미지 인덱스, 없으면 -1)
    }
  ]
}
`;

    const openaiResponse = await axios.post('https://api.openai.com/v1/chat/completions', {
      model: 'gpt-4',
      messages: [
        { role: 'system', content: systemPrompt },
        { role: 'user', content: userContent }
      ],
      temperature: 0.7
    }, {
      headers: {
        'Authorization': `Bearer ${process.env.OPENAI_API_KEY}`,
        'Content-Type': 'application/json'
      }
    });

    let scriptData;
    try {
      const responseText = openaiResponse.data.choices[0].message.content;
      const jsonMatch = responseText.match(/\{[\s\S]*\}/);
      scriptData = JSON.parse(jsonMatch ? jsonMatch[0] : responseText);
    } catch (parseError) {
      throw new Error('스크립트 파싱 실패');
    }

    // 이미지 매칭
    const scenes = scriptData.scenes.map(scene => ({
      ...scene,
      image: scene.imageIndex >= 0 && content.images && content.images[scene.imageIndex] 
        ? content.images[scene.imageIndex] 
        : null
    }));

    res.json({
      success: true,
      scenes
    });

  } catch (error) {
    console.error('Script generation error:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// POST /api/advanced/generate-video - 비디오 생성
router.post('/generate-video', async (req, res) => {
  try {
    const { scenes, settings, character, mode } = req.body;
    const jobId = `advanced_${Date.now()}`;

    // 작업 상태 초기화
    jobs.set(jobId, {
      status: 'processing',
      progress: 0,
      message: '비디오 생성 시작...'
    });

    res.json({
      success: true,
      jobId,
      estimatedTime: '2-5분'
    });

    // 백그라운드에서 비디오 생성
    generateAdvancedVideo(jobId, scenes, settings, character, mode).catch(err => {
      console.error('Video generation error:', err);
      jobs.set(jobId, {
        status: 'failed',
        error: err.message
      });
    });

  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// GET /api/advanced/status/:jobId - 작업 상태 확인
router.get('/status/:jobId', (req, res) => {
  const { jobId } = req.params;
  const job = jobs.get(jobId);

  if (!job) {
    return res.json({
      success: false,
      status: 'not_found'
    });
  }

  res.json({
    success: true,
    ...job
  });
});

// 비디오 생성 함수
async function generateAdvancedVideo(jobId, scenes, settings, character, mode) {
  const OUTPUT_DIR = '/mnt/music-storage/shorts-videos/output';
  const AUDIO_DIR = '/mnt/music-storage/shorts-videos/audio';
  
  try {
    jobs.set(jobId, {
      status: 'processing',
      progress: 10,
      message: '장면 준비 중...'
    });

    // 1. 각 장면에 대해 TTS 생성
    const sceneVideos = [];
    for (let i = 0; i < scenes.length; i++) {
      const scene = scenes[i];
      
      jobs.set(jobId, {
        status: 'processing',
        progress: 10 + (i / scenes.length * 40),
        message: `장면 ${i + 1}/${scenes.length} 음성 생성 중...`
      });

      // TTS 생성
      const audioPath = path.join(AUDIO_DIR, `${jobId}_scene_${i + 1}.mp3`);
      await execAsync(`gtts-cli "${scene.text.replace(/\n/g, ' ')}" --lang ko --output "${audioPath}"`);

      // 2. 이미지가 있으면 다운로드
      let imagePath;
      if (scene.image && mode !== 'character') {
        imagePath = path.join(OUTPUT_DIR, `${jobId}_img_${i + 1}.jpg`);
        await execAsync(`curl -s "${scene.image}" -o "${imagePath}"`);
      } else {
        // 캐릭터 또는 그라데이션 배경 생성
        const colors = [
          ['#FF6B9D', '#C44569'],
          ['#4A69BD', '#1E3799'],
          ['#26de81', '#20bf6b'],
          ['#FD7272', '#FC5C65'],
          ['#A55EEA', '#8854d0']
        ];
        const [c1, c2] = colors[i % colors.length];
        imagePath = path.join(OUTPUT_DIR, `${jobId}_bg_${i + 1}.jpg`);
        await execAsync(`convert -size 1080x1920 gradient:"${c1}-${c2}" "${imagePath}"`);
      }

      jobs.set(jobId, {
        status: 'processing',
        progress: 50 + (i / scenes.length * 30),
        message: `장면 ${i + 1}/${scenes.length} 비디오 생성 중...`
      });

      // 3. 비디오 장면 생성 (자막 포함)
      const font = `/usr/share/fonts/truetype/nanum/${settings.font || 'NanumGothicBold'}.ttf`;
      const fontSize = settings.subtitleSize || 75;
      const subtitleY = 1920 - (settings.subtitlePosition || 400);
      const borderWidth = settings.subtitleBorder || 6;

      const lines = scene.text.split('\n');
      const escapedLines = lines.map(line => line.replace(/"/g, '\\"').replace(/'/g, "\\'"));
      
      let drawtextFilters = '';
      const lineHeight = fontSize * 1.2;
      
      for (let j = 0; j < escapedLines.length; j++) {
        const y = subtitleY + (j * lineHeight);
        drawtextFilters += `drawtext=text='${escapedLines[j]}':fontfile=${font}:fontsize=${fontSize}:fontcolor=white:borderw=${borderWidth}:bordercolor=black:x=(w-text_w)/2:y=${y},`;
      }
      drawtextFilters = drawtextFilters.slice(0, -1);

      const outputVideo = path.join(OUTPUT_DIR, `${jobId}_scene_${i + 1}.mp4`);
      const duration = scene.duration || 6;
      
      await execAsync(`ffmpeg -loop 1 -i "${imagePath}" -i "${audioPath}" \
        -filter_complex "[0:v]scale=1080:1920:force_original_aspect_ratio=increase,crop=1080:1920,zoompan=z='min(1.0+0.1*sin(in_time*2*PI/${duration}),1.15)':d=${duration * 25}:s=1080x1920:fps=25,${drawtextFilters}[v]" \
        -map "[v]" -map 1:a \
        -c:v libx264 -preset fast -pix_fmt yuv420p -t ${duration} \
        -c:a aac -b:a 192k -shortest \
        -y "${outputVideo}"`);

      sceneVideos.push(outputVideo);
    }

    jobs.set(jobId, {
      status: 'processing',
      progress: 85,
      message: '최종 병합 중...'
    });

    // 4. 모든 장면 병합
    const concatFile = path.join(OUTPUT_DIR, `${jobId}_concat.txt`);
    await fs.writeFile(concatFile, sceneVideos.map(v => `file '${v}'`).join('\n'));

    const finalOutput = path.join(OUTPUT_DIR, `${jobId}_FINAL.mp4`);
    
    // 배경 음악이 있으면 추가
    let ffmpegCmd = `ffmpeg -f concat -safe 0 -i "${concatFile}"`;
    
    if (settings.bgm && settings.bgm.length > 0) {
      ffmpegCmd += ` -i "${settings.bgm[0].path}" -filter_complex "[1:a]volume=0.3[bgm];[0:a][bgm]amix=inputs=2:duration=first[aout]" -map 0:v -map "[aout]"`;
    } else {
      ffmpegCmd += ` -map 0:v -map 0:a`;
    }
    
    ffmpegCmd += ` -c:v libx264 -profile:v baseline -level 3.0 -preset fast \
      -pix_fmt yuv420p -movflags +faststart \
      -c:a aac -b:a 192k -y "${finalOutput}"`;

    await execAsync(ffmpegCmd);

    // 5. 유튜브 메타데이터 생성
    const youtubeData = await generateYoutubeMetadata(scenes, settings);

    jobs.set(jobId, {
      status: 'completed',
      progress: 100,
      message: '완료!',
      videoUrl: `https://ai-shorts.neuralgrid.kr/videos/${path.basename(finalOutput)}`,
      ...youtubeData
    });

    console.log(`✅ Advanced video completed: ${jobId}`);

  } catch (error) {
    console.error(`❌ Advanced video failed: ${jobId}`, error);
    jobs.set(jobId, {
      status: 'failed',
      error: error.message
    });
  }
}

// 유튜브 메타데이터 생성
async function generateYoutubeMetadata(scenes, settings) {
  const title = scenes[0]?.text?.substring(0, 50) + '... #Shorts';
  const description = scenes.map((s, i) => `${i + 1}. ${s.text}`).join('\n\n');
  const tags = '쇼츠, Shorts, AI, 자동화, 영상제작';

  return {
    youtubeTitle: title,
    youtubeDescription: description + '\n\n#Shorts #AI #자동화영상',
    youtubeTags: tags
  };
}

module.exports = router;
