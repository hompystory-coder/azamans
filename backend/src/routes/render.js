// 로컬 FFmpeg 기반 최종 렌더링 API
// Shotstack API 대체 - 완전 무료
import express from 'express';
import videoRenderer from '../utils/videoRenderer.js';

const router = express.Router();

/**
 * POST /api/render/final
 * 로컬 FFmpeg로 최종 렌더링
 * - Shotstack API 비용 제로
 * - 빠른 로컬 처리
 */
router.post('/final', async (req, res) => {
  try {
    const { scenes, settings } = req.body;

    if (!scenes || scenes.length === 0) {
      return res.status(400).json({
        success: false,
        error: '장면 데이터가 필요합니다.'
      });
    }

    console.log(`🎬 로컬 FFmpeg 최종 렌더링 시작: ${scenes.length}개 장면`);
    console.log(`💰 Shotstack API 비용 절감: ₩0 (무료!)`);

    // 렌더 ID 생성
    const renderId = `render_${Date.now()}_${Math.random().toString(36).substring(2, 8)}`;

    // 즉시 응답
    res.json({
      success: true,
      data: {
        renderId,
        status: 'processing',
        message: '로컬 FFmpeg로 최종 렌더링 시작 (API 비용 ₩0)',
        estimatedTime: `약 ${scenes.length * 10}초`,
        checkUrl: `/api/render/status/${renderId}`
      }
    });

    // 백그라운드 처리
    (async () => {
      try {
        console.log(`🚀 백그라운드 렌더링 시작: ${renderId}`);

        // 로컬 FFmpeg로 비디오 생성
        const result = await videoRenderer.generateVideo(scenes, settings);

        console.log(`✅ 렌더링 완료: ${renderId}`);
        console.log(`   Video URL: ${result.videoUrl}`);
        console.log(`   Size: ${(result.size / 1024 / 1024).toFixed(2)} MB`);
        console.log(`   비용: ₩0 (Shotstack 대비 100% 절감!)`);

        // 상태 업데이트
        global.renderStatus = global.renderStatus || {};
        global.renderStatus[renderId] = {
          status: 'completed',
          progress: 100,
          videoUrl: result.videoUrl,
          videoId: result.videoId,
          size: result.size,
          message: '렌더링 완료!',
          cost: 0
        };

      } catch (error) {
        console.error(`❌ 렌더링 실패: ${renderId}`, error);
        
        global.renderStatus = global.renderStatus || {};
        global.renderStatus[renderId] = {
          status: 'failed',
          progress: 0,
          error: error.message
        };
      }
    })();

  } catch (error) {
    console.error('❌ 렌더링 요청 오류:', error);
    res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

/**
 * GET /api/render/status/:renderId
 * 렌더링 상태 확인
 */
router.get('/status/:renderId', (req, res) => {
  try {
    const { renderId } = req.params;
    
    const status = (global.renderStatus || {})[renderId];

    if (!status) {
      return res.json({
        success: true,
        data: {
          renderId,
          status: 'processing',
          progress: 50,
          message: '렌더링 중...'
        }
      });
    }

    res.json({
      success: true,
      data: {
        renderId,
        ...status
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
 * GET /api/render/cost-comparison
 * Shotstack vs 로컬 FFmpeg 비용 비교
 */
router.get('/cost-comparison', (req, res) => {
  const { videos = 100 } = req.query;
  
  // Shotstack 평균 비용 (HD 비디오 기준)
  const shotstackCostPerVideo = 0.05; // $0.05 per video
  const shotstackTotal = videos * shotstackCostPerVideo;
  
  // 로컬 FFmpeg 비용
  const localTotal = 0; // 무료!
  
  const savings = shotstackTotal;
  const savingsPercent = 100;

  res.json({
    success: true,
    data: {
      videos: parseInt(videos),
      shotstack: {
        costPerVideo: shotstackCostPerVideo,
        totalCost: shotstackTotal,
        currency: 'USD'
      },
      localFFmpeg: {
        costPerVideo: 0,
        totalCost: 0,
        currency: 'USD'
      },
      savings: {
        amount: savings,
        percent: savingsPercent,
        message: `${videos}개 비디오 생성 시 $${savings.toFixed(2)} 절감!`
      }
    }
  });
});

export default router;
