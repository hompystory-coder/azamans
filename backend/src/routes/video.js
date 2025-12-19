// 로컬 FFmpeg 기반 비디오 생성 API
// Shotstack API 비용 제로 - 완전 무료
import express from 'express';
import videoRenderer from '../utils/videoRenderer.js';
import multer from 'multer';

const router = express.Router();


// Multer 설정 (FormData 파싱용)
const upload = multer({
  storage: multer.memoryStorage(),
  limits: { fileSize: 50 * 1024 * 1024 } // 50MB
});
// 비디오 생성 상태 저장 (프로덕션에서는 Redis 사용 권장)
const videoJobs = new Map();

/**
 * POST /api/video/generate
 * 로컬 FFmpeg로 비디오 생성
 * - API 비용 제로
 * - 빠른 처리 속도
 * - 완전한 커스터마이징
 */
router.post('/generate', upload.fields([{ name: 'bgMusicFile' }, { name: 'bgImageFile' }]), async (req, res) => {
  try {
    // === 디버깅: 요청 데이터 로깅 ===
    console.log('📦 받은 요청 body:', JSON.stringify(req.body, null, 2));
    console.log('📦 scenes 타입:', typeof req.body.scenes);
    console.log('📦 scenes 값:', req.body.scenes);
    console.log('📦 scenes 길이:', req.body.scenes?.length);
    // === 디버깅 끝 ===
    
    // FormData에서 JSON 파싱 처리
    let scenes = req.body.scenes;
    let settings = req.body.settings;
    
    // FormData로 전송된 경우 JSON 파싱
    if (typeof scenes === 'string') {
      try {
        scenes = JSON.parse(scenes);
      } catch (e) {
        console.error('❌ scenes JSON 파싱 실패:', e);
      }
    }
    
    // parts를 scenes로 변환 (호환성)
    if (!scenes && req.body.parts) {
      console.log('📝 parts를 scenes로 변환');
      let parts = req.body.parts;
      if (typeof parts === 'string') {
        try {
          parts = JSON.parse(parts);
        } catch (e) {
          console.error('❌ parts JSON 파싱 실패:', e);
        }
      }
      scenes = parts;
    }
    
    if (typeof settings === 'string') {
      try {
        settings = JSON.parse(settings);
      } catch (e) {
        console.error('❌ settings JSON 파싱 실패:', e);
      }
    }

    // 입력 검증
    if (!scenes || scenes.length === 0) {
      return res.status(400).json({
        success: false,
        error: '장면 데이터가 필요합니다.'
      });
    }

    // 각 장면에 필수 필드 확인 및 imageUrl 프록시 처리
    for (let i = 0; i < scenes.length; i++) {
      const scene = scenes[i];
      if (!scene.imageUrl) {
        return res.status(400).json({
          success: false,
          error: `장면 ${i + 1}에 이미지가 필요합니다.`
        });
      }
      
      // /api/image-proxy?url=... 형식을 원본 URL로 변환
      if (scene.imageUrl.startsWith('/api/image-proxy?url=')) {
        try {
          const urlParam = scene.imageUrl.split('url=')[1];
          scene.imageUrl = decodeURIComponent(urlParam);
          console.log(`📝 장면 ${i + 1}: 프록시 URL을 원본 URL로 변환`);
        } catch (e) {
          console.error(`❌ 장면 ${i + 1}: URL 디코딩 실패`, e);
        }
      }
      
      // scene.text 또는 scene.narration을 subtitle로 매핑 (자막 활성화 시)
      // subtitle 객체가 있으면 자막 활성화로 간주 (enabled 체크 안함)
      if (settings?.subtitle) {
        const text = scene.text || scene.narration;
        if (text) {
          scene.subtitle = text;
          console.log(`📝 장면 ${i + 1}: 자막 추가 "${text.substring(0, 20)}..."`);
        }
      }
      
      // 글로벌 제목을 각 장면에 추가 (제목 활성화 시)
      // title 또는 titleConfig 둘 다 지원
      const titleSettings = settings?.title || settings?.titleConfig;
      if (titleSettings?.enabled && titleSettings?.text) {
        scene.title = titleSettings.text;
        console.log(`📝 장면 ${i + 1}: 제목 추가 "${titleSettings.text.substring(0, 30)}..."`);
      }
      
      //       if (!scene.audioUrl) {
      //         return res.status(400).json({
      //           success: false,
      //           error: `장면 ${i + 1}에 음성이 필요합니다.`
      //         });
      //       }
    }

    // 업로드된 파일 처리
    if (req.files) {
      const fs = await import('fs/promises');
      const path = await import('path');
      
      // 배경 음악 파일 처리
      if (req.files.bgMusicFile && req.files.bgMusicFile[0]) {
        const bgMusicFile = req.files.bgMusicFile[0];
        const bgMusicPath = path.join('/tmp/uploads/music', `${Date.now()}_${bgMusicFile.originalname}`);
        await fs.writeFile(bgMusicPath, bgMusicFile.buffer);
        
        if (!settings.bgMusic) settings.bgMusic = {};
        settings.bgMusic.url = bgMusicPath;
        console.log(`🎵 배경 음악 파일 저장: ${bgMusicPath}`);
      }
      
      // 배경 이미지 파일 처리
      if (req.files.bgImageFile && req.files.bgImageFile[0]) {
        const bgImageFile = req.files.bgImageFile[0];
        const bgImagePath = path.join('/tmp/uploads/backgrounds', `${Date.now()}_${bgImageFile.originalname}`);
        await fs.writeFile(bgImagePath, bgImageFile.buffer);
        
        if (!settings.bgImage) settings.bgImage = {};
        settings.bgImage.url = bgImagePath;
        console.log(`🖼️  배경 이미지 파일 저장: ${bgImagePath}`);
      }
    }

    // settings 키 이름 정규화 (frontend와 renderer 간 호환성)
    // frontend: subtitle, titleConfig → renderer: subtitleSettings, titleSettings
    if (settings.subtitle) {
      settings.subtitleSettings = settings.subtitle;
    }
    if (settings.titleConfig) {
      settings.titleSettings = settings.titleConfig;
    } else if (settings.title && typeof settings.title === 'object' && !settings.title.text) {
      // settings.title이 설정 객체인 경우 (text가 없으면)
      settings.titleSettings = settings.title;
    }

    console.log(`🎬 로컬 FFmpeg 비디오 생성 시작: ${scenes.length}개 장면`);
    console.log(`💰 API 비용: ₩0 (무료!)`);
    console.log(`📦 scenes 데이터:`, JSON.stringify(scenes.map(s => ({
      text: s.text,
      imageUrl: s.imageUrl?.substring(0, 50) + '...',
      audioUrl: s.audioUrl ? '있음' : '없음',
      duration: s.duration
    })), null, 2));
    console.log(`📦 settings 데이터:`, JSON.stringify(settings, null, 2));

    // 비디오 ID 생성
    const videoId = `video_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;

    // 초기 상태 저장
    videoJobs.set(videoId, {
      status: 'processing',
      progress: 0,
      message: '비디오 생성 준비 중...',
      startTime: Date.now(),
      scenes: scenes.length
    });

    // 즉시 응답 (백그라운드에서 처리)
    res.json({
      success: true,
      data: {
        videoId,
        status: 'processing',
        message: `로컬 FFmpeg로 비디오 생성 시작! (API 비용 ₩0)`,
        estimatedTime: `약 ${scenes.length * 10}초`,
        checkUrl: `/api/video/status/${videoId}`
      }
    });

    // 백그라운드에서 비디오 생성
    (async () => {
      try {
        console.log(`🚀 백그라운드 비디오 생성 시작: ${videoId}`);

        // 진행률 업데이트
        videoJobs.set(videoId, {
          ...videoJobs.get(videoId),
          progress: 10,
          message: '장면 비디오 생성 중...'
        });

        // 비디오 생성 (로컬 FFmpeg 사용)
        const result = await videoRenderer.generateVideo(scenes, settings);

        // 완료 상태 업데이트
        const endTime = Date.now();
        const duration = Math.round((endTime - videoJobs.get(videoId).startTime) / 1000);

        videoJobs.set(videoId, {
          status: 'completed',
          progress: 100,
          message: '비디오 생성 완료!',
          videoUrl: result.videoUrl,
          videoPath: result.videoPath,
          videoId: result.videoId,
          size: result.size,
          duration: result.duration,
          processingTime: duration,
          cost: 0 // 무료!
        });

        console.log(`✅ 비디오 생성 완료: ${videoId}`);
        console.log(`   처리 시간: ${duration}초`);
        console.log(`   파일 크기: ${(result.size / 1024 / 1024).toFixed(2)} MB`);
        console.log(`   비용: ₩0 (무료!)`);

      } catch (error) {
        console.error(`❌ 비디오 생성 실패: ${videoId}`, error);
        
        videoJobs.set(videoId, {
          status: 'failed',
          progress: 0,
          message: '비디오 생성 실패',
          error: error.message
        });
      }
    })();

  } catch (error) {
    console.error('❌ 비디오 생성 요청 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * GET /api/video/status/:videoId
 * 비디오 생성 상태 확인
 */
router.get('/status/:videoId', (req, res) => {
  try {
    const { videoId } = req.params;
    
    const job = videoJobs.get(videoId);

    if (!job) {
      return res.json({
        success: true,
        data: {
          videoId,
          status: 'not_found',
          progress: 0,
          message: '비디오 작업을 찾을 수 없습니다.'
        }
      });
    }

    res.json({
      success: true,
      data: {
        videoId,
        ...job
      }
    });

  } catch (error) {
    console.error('❌ 상태 확인 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * POST /api/video/generate-scene
 * 단일 장면 비디오 생성 (테스트용)
 */
router.post('/generate-scene', async (req, res) => {
  try {
    const { scene, settings } = req.body;

    if (!scene || !scene.imageUrl || !scene.audioUrl) {
      return res.status(400).json({
        success: false,
        error: '장면 데이터(이미지, 음성)가 필요합니다.'
      });
    }

    console.log('🎬 단일 장면 비디오 생성...');

    // 단일 장면 생성
    const scenePath = await videoRenderer.createSceneVideo(scene, 0, settings);

    res.json({
      success: true,
      data: {
        scenePath,
        message: '장면 비디오 생성 완료'
      }
    });

  } catch (error) {
    console.error('❌ 장면 생성 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * DELETE /api/video/:videoId
 * 비디오 삭제
 */
router.delete('/:videoId', async (req, res) => {
  try {
    const { videoId } = req.params;
    
    const job = videoJobs.get(videoId);
    
    if (!job) {
      return res.status(404).json({
        success: false,
        error: '비디오를 찾을 수 없습니다.'
      });
    }

    if (job.videoPath) {
      const fs = await import('fs/promises');
      try {
        await fs.unlink(job.videoPath);
        console.log(`🗑️  비디오 파일 삭제: ${job.videoPath}`);
      } catch (error) {
        console.error('파일 삭제 실패:', error);
      }
    }

    videoJobs.delete(videoId);

    res.json({
      success: true,
      message: '비디오가 삭제되었습니다.'
    });

  } catch (error) {
    console.error('❌ 비디오 삭제 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * GET /api/video/jobs/list
 * 모든 비디오 작업 목록
 */
router.get('/jobs/list', (req, res) => {
  try {
    const jobs = Array.from(videoJobs.entries()).map(([id, job]) => ({
      videoId: id,
      ...job
    }));

    res.json({
      success: true,
      data: {
        total: jobs.length,
        jobs: jobs.sort((a, b) => b.startTime - a.startTime)
      }
    });

  } catch (error) {
    console.error('❌ 작업 목록 조회 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * POST /api/video/jobs/cleanup
 * 완료된 작업 정리
 */
router.post('/jobs/cleanup', (req, res) => {
  try {
    let cleaned = 0;
    const now = Date.now();
    const maxAge = 24 * 60 * 60 * 1000; // 24시간

    for (const [videoId, job] of videoJobs.entries()) {
      if (job.status === 'completed' || job.status === 'failed') {
        if (now - job.startTime > maxAge) {
          videoJobs.delete(videoId);
          cleaned++;
        }
      }
    }

    res.json({
      success: true,
      message: `${cleaned}개의 오래된 작업을 정리했습니다.`
    });

  } catch (error) {
    console.error('❌ 작업 정리 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// 정기적으로 오래된 작업 정리 (1시간마다)
setInterval(() => {
  const now = Date.now();
  const maxAge = 24 * 60 * 60 * 1000;
  let cleaned = 0;

  for (const [videoId, job] of videoJobs.entries()) {
    if (job.status === 'completed' || job.status === 'failed') {
      if (now - job.startTime > maxAge) {
        videoJobs.delete(videoId);
        cleaned++;
      }
    }
  }

  if (cleaned > 0) {
    console.log(`🗑️  ${cleaned}개의 오래된 비디오 작업 정리 완료`);
  }
}, 60 * 60 * 1000);

export default router;
