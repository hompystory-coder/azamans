import express from 'express';
import axios from 'axios';

const router = express.Router();

/**
 * 이미지 프록시 엔드포인트
 * Naver 블로그 이미지 등 리퍼러 체크가 있는 이미지를 프록시
 * 
 * 사용법: 
 * GET /api/image-proxy?url=https://postfiles.pstatic.net/.../image.png
 */
router.get('/', async (req, res) => {
  try {
    const { url } = req.query;

    if (!url) {
      return res.status(400).json({
        success: false,
        error: '이미지 URL이 필요합니다',
        usage: '/api/image-proxy?url=IMAGE_URL'
      });
    }

    // URL 검증
    let imageUrl;
    try {
      imageUrl = new URL(url);
    } catch (error) {
      return res.status(400).json({
        success: false,
        error: '유효하지 않은 URL입니다'
      });
    }

    console.log(`[Image Proxy] Fetching image: ${url}`);

    // 적절한 리퍼러 헤더로 이미지 가져오기
    const response = await axios.get(url, {
      responseType: 'arraybuffer',
      headers: {
        'Referer': 'https://blog.naver.com/',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
      },
      timeout: 10000, // 10초 타임아웃
      maxRedirects: 5
    });

    // Content-Type 전달
    const contentType = response.headers['content-type'] || 'image/jpeg';
    res.set('Content-Type', contentType);

    // 캐시 헤더 추가 (1시간)
    res.set('Cache-Control', 'public, max-age=3600');

    // CORS 헤더 추가
    res.set('Access-Control-Allow-Origin', '*');

    // 이미지 데이터 전송
    res.send(Buffer.from(response.data));

    console.log(`[Image Proxy] ✓ Success: ${url} (${contentType})`);

  } catch (error) {
    console.error('[Image Proxy] Error:', error.message);

    if (error.response) {
      // 원본 서버에서 에러 반환
      return res.status(error.response.status).json({
        success: false,
        error: '이미지를 가져올 수 없습니다',
        details: error.message
      });
    } else if (error.code === 'ECONNABORTED') {
      // 타임아웃
      return res.status(504).json({
        success: false,
        error: '이미지 로딩 시간 초과'
      });
    } else {
      // 기타 에러
      return res.status(500).json({
        success: false,
        error: '이미지 프록시 오류',
        details: error.message
      });
    }
  }
});

export default router;
