// 이미지 프록시 API - 리퍼러 우회
import express from 'express';
import axios from 'axios';

const router = express.Router();

// GET /api/image-proxy?url=<image_url>
router.get('/', async (req, res) => {
  try {
    const { url } = req.query;
    
    if (!url) {
      return res.status(400).json({
        success: false,
        error: 'Image URL is required'
      });
    }

    console.log(`🖼️  이미지 프록시 요청: ${url}`);

    // 이미지 다운로드 (리퍼러 포함)
    const response = await axios.get(url, {
      headers: {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Referer': 'https://blog.naver.com/',
        'Accept': 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Accept-Language': 'ko-KR,ko;q=0.9,en-US;q=0.8,en;q=0.7',
        'Cache-Control': 'no-cache'
      },
      responseType: 'arraybuffer',
      timeout: 10000,
      maxRedirects: 5
    });

    // Content-Type 설정
    const contentType = response.headers['content-type'] || 'image/jpeg';
    
    // 캐시 헤더 설정 (1시간)
    res.set({
      'Content-Type': contentType,
      'Cache-Control': 'public, max-age=3600',
      'Access-Control-Allow-Origin': '*'
    });

    // 이미지 데이터 전송
    res.send(Buffer.from(response.data));
    
    console.log(`✅ 이미지 프록시 성공: ${url.substring(0, 80)}...`);

  } catch (error) {
    console.error('❌ 이미지 프록시 오류:', error.message);
    
    // 에러 응답
    res.status(error.response?.status || 500).json({
      success: false,
      error: error.message || 'Failed to fetch image'
    });
  }
});

export default router;
