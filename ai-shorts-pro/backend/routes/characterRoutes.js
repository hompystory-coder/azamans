const express = require('express');
const router = express.Router();
const { exec } = require('child_process');
const { promisify } = require('util');
const execAsync = promisify(exec);
const path = require('path');
const fs = require('fs').promises;

// 사용 가능한 캐릭터 목록
const CHARACTERS = {
  sophia: {
    id: 'sophia',
    name: '소피아',
    description: '친근한 20대 여성 AI 쇼핑 도우미',
    personality: '밝고 활기찬 성격',
    voice: 'female',
    avatar: '👩‍💼'
  },
  james: {
    id: 'james',
    name: '제임스',
    description: '전문적인 30대 남성 AI 비즈니스 전문가',
    personality: '신뢰감 있고 전문적인',
    voice: 'male',
    avatar: '👨‍💼'
  },
  mina: {
    id: 'mina',
    name: '미나',
    description: '발랄한 10대 후반 여성 AI 인플루언서',
    personality: '활발하고 트렌디한',
    voice: 'female',
    avatar: '👧'
  }
};

// GET /api/character/list - 캐릭터 목록 조회
router.get('/list', (req, res) => {
  res.json({
    success: true,
    characters: Object.values(CHARACTERS)
  });
});

// POST /api/character/shorts - 캐릭터 쇼츠 생성
router.post('/shorts', async (req, res) => {
  try {
    const { url, characterId, title } = req.body;

    if (!url) {
      return res.status(400).json({
        success: false,
        error: 'URL is required'
      });
    }

    if (!characterId || !CHARACTERS[characterId]) {
      return res.status(400).json({
        success: false,
        error: 'Valid character ID is required'
      });
    }

    const character = CHARACTERS[characterId];

    // 쇼츠 생성 작업을 비동기로 시작
    const jobId = `job_${Date.now()}`;
    
    // 응답 먼저 보내기
    res.json({
      success: true,
      jobId,
      message: '캐릭터 쇼츠 생성이 시작되었습니다',
      character: character.name,
      estimatedTime: '30-60초'
    });

    // 백그라운드에서 쇼츠 생성
    generateCharacterShorts(url, character, title, jobId).catch(err => {
      console.error('Shorts generation error:', err);
    });

  } catch (error) {
    console.error('Character shorts error:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// 캐릭터 쇼츠 생성 함수
async function generateCharacterShorts(url, character, title, jobId) {
  const OUTPUT_DIR = '/mnt/music-storage/shorts-videos/output';
  const AUDIO_DIR = '/mnt/music-storage/shorts-videos/audio';
  
  console.log(`🎬 ${character.name} 캐릭터 쇼츠 생성 시작 - Job: ${jobId}`);
  
  try {
    // 1. 블로그 크롤링
    console.log('📥 블로그 크롤링 중...');
    const axios = require('axios');
    const crawlResponse = await axios.post('http://localhost:5555/api/crawler/crawl', { url });
    const crawledData = crawlResponse.data.data || crawlResponse.data;
    
    console.log(`✅ 크롤링 완료: ${crawledData.title}`);
    
    // 2. 스크립트 생성 (간단한 5개 장면)
    const productName = title || crawledData.title?.split(':')[0] || '제품';
    const scenes = [
      { 
        text: `안녕하세요!\n저는 ${character.name}예요`,
        duration: 6 
      },
      { 
        text: `오늘은 ${productName}\n소개해드릴게요`,
        duration: 6 
      },
      { 
        text: "특별한 기능들이\n정말 많답니다",
        duration: 6 
      },
      { 
        text: "가격 대비 성능이\n훌륭해요",
        duration: 6 
      },
      { 
        text: "좋아요와 구독\n잊지 마세요!",
        duration: 6 
      }
    ];
    
    // 3. 각 장면 생성
    const sceneVideos = [];
    const colors = [
      ['#FF6B9D', '#C44569'],
      ['#4A69BD', '#1E3799'],
      ['#26de81', '#20bf6b'],
      ['#FD7272', '#FC5C65'],
      ['#A55EEA', '#8854d0']
    ];
    
    for (let i = 0; i < scenes.length; i++) {
      const scene = scenes[i];
      console.log(`🎬 장면 ${i+1}/${scenes.length} 생성 중...`);
      
      // TTS 생성
      const cleanText = scene.text.replace(/\n/g, ' ');
      const audioPath = path.join(AUDIO_DIR, `${jobId}_scene_${i+1}.mp3`);
      await execAsync(`gtts-cli "${cleanText}" --lang ko --output "${audioPath}"`);
      
      // 캐릭터 배경 이미지 생성
      const [c1, c2] = colors[i];
      const imagePath = path.join(OUTPUT_DIR, `${jobId}_char_${i+1}.jpg`);
      await execAsync(`convert -size 1080x1920 gradient:"${c1}-${c2}" \
        -gravity center \
        \\( -size 600x1200 xc:none -fill white -draw "ellipse 300,600 250,500 0,360" \\) \
        -compose Over -composite "${imagePath}"`);
      
      // 비디오 생성 (자막 포함)
      const lines = scene.text.split('\n');
      const escapedLines = lines.map(line => line.replace(/"/g, '\\"').replace(/'/g, "\\'"));
      
      let drawtextFilters = '';
      const lineHeight = 85;
      const startY = 1920 - 450;
      
      for (let j = 0; j < escapedLines.length; j++) {
        const y = startY + (j * lineHeight);
        drawtextFilters += `drawtext=text='${escapedLines[j]}':fontfile=/usr/share/fonts/truetype/nanum/NanumGothicBold.ttf:fontsize=75:fontcolor=white:borderw=6:bordercolor=black:x=(w-text_w)/2:y=${y}:shadowcolor=black:shadowx=3:shadowy=3,`;
      }
      drawtextFilters = drawtextFilters.slice(0, -1);
      
      const outputVideo = path.join(OUTPUT_DIR, `${jobId}_scene_${i+1}.mp4`);
      const zoomEffect = `zoompan=z='min(1.0+0.1*sin(in_time*2*PI/6),1.15)':d=150:s=1080x1920:fps=25`;
      
      await execAsync(`ffmpeg -loop 1 -i "${imagePath}" -i "${audioPath}" \
        -filter_complex "[0:v]${zoomEffect},eq=brightness=0.1:contrast=1.1,${drawtextFilters}[v]" \
        -map "[v]" -map 1:a \
        -c:v libx264 -preset fast -tune stillimage \
        -pix_fmt yuv420p -t 6 \
        -c:a aac -b:a 192k -shortest \
        -y "${outputVideo}"`);
      
      sceneVideos.push(outputVideo);
      console.log(`✅ 장면 ${i+1} 완료`);
    }
    
    // 4. 최종 병합
    console.log('🎞️ 최종 병합 중...');
    const concatFile = path.join(OUTPUT_DIR, `${jobId}_concat.txt`);
    const concatContent = sceneVideos.map(v => `file '${v}'`).join('\n');
    await fs.writeFile(concatFile, concatContent);
    
    const finalOutput = path.join(OUTPUT_DIR, `${jobId}_FINAL.mp4`);
    await execAsync(`ffmpeg -f concat -safe 0 -i "${concatFile}" \
      -c:v libx264 -profile:v baseline -level 3.0 -preset fast \
      -pix_fmt yuv420p -movflags +faststart \
      -c:a aac -b:a 192k -y "${finalOutput}"`);
    
    const stats = await fs.stat(finalOutput);
    console.log(`✅ ${character.name} 쇼츠 생성 완료: ${finalOutput}`);
    console.log(`📦 크기: ${(stats.size/1024).toFixed(0)} KB`);
    console.log(`🌐 다운로드: https://ai-shorts.neuralgrid.kr/videos/${path.basename(finalOutput)}`);
    
    // 작업 완료 상태 저장 (간단하게 파일로)
    const resultFile = path.join(OUTPUT_DIR, `${jobId}_result.json`);
    await fs.writeFile(resultFile, JSON.stringify({
      success: true,
      jobId,
      character: character.name,
      videoUrl: `https://ai-shorts.neuralgrid.kr/videos/${path.basename(finalOutput)}`,
      size: stats.size,
      duration: 30,
      timestamp: new Date().toISOString()
    }));
    
  } catch (error) {
    console.error(`❌ ${character.name} 쇼츠 생성 실패:`, error);
    
    // 에러 상태 저장
    const resultFile = path.join(OUTPUT_DIR, `${jobId}_result.json`);
    await fs.writeFile(resultFile, JSON.stringify({
      success: false,
      jobId,
      error: error.message,
      timestamp: new Date().toISOString()
    }));
  }
}

// GET /api/character/status/:jobId - 작업 상태 조회
router.get('/status/:jobId', async (req, res) => {
  try {
    const { jobId } = req.params;
    const OUTPUT_DIR = '/mnt/music-storage/shorts-videos/output';
    const resultFile = path.join(OUTPUT_DIR, `${jobId}_result.json`);
    
    try {
      const data = await fs.readFile(resultFile, 'utf8');
      const result = JSON.parse(data);
      res.json(result);
    } catch (error) {
      // 파일이 없으면 아직 처리 중
      res.json({
        success: false,
        jobId,
        status: 'processing',
        message: '쇼츠 생성 중입니다...'
      });
    }
  } catch (error) {
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

module.exports = router;
