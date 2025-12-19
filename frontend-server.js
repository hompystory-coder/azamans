import express from 'express';
import axios from 'axios';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3006;
const BACKEND_PORT = process.env.BACKEND_PORT || 4001;
const BACKEND_URL = `http://localhost:${BACKEND_PORT}`;

// Body parser for JSON
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Video files proxy - forward /shorts-videos/* to backend
app.all(/^\/shorts-videos\/.*/, async (req, res) => {
  const videoPath = req.originalUrl;
  const targetUrl = `${BACKEND_URL}${videoPath}`;
  
  console.log(`🎬 Video Proxy: ${req.method} ${videoPath} → ${targetUrl}`);
  
  try {
    const response = await axios({
      method: req.method,
      url: targetUrl,
      responseType: 'stream', // 비디오 스트리밍
      timeout: 60000 // 1 minute
    });
    
    // 헤더 복사
    res.set('Content-Type', response.headers['content-type'] || 'video/mp4');
    res.set('Content-Length', response.headers['content-length']);
    res.set('Accept-Ranges', response.headers['accept-ranges'] || 'bytes');
    res.set('Cache-Control', 'public, max-age=3600');
    res.set('Access-Control-Allow-Origin', '*');
    
    // 스트림 전달
    response.data.pipe(res);
    
    console.log(`✅ Video proxy success for ${videoPath}`);
  } catch (error) {
    console.error(`❌ Video proxy error for ${videoPath}:`, error.message);
    res.status(error.response?.status || 500).json({
      success: false,
      error: 'Video proxy error',
      message: error.message
    });
  }
});

// Manual API proxy - forward all /api/* requests to backend
app.all(/^\/api\/.*/, async (req, res) => {
  const apiPath = req.originalUrl; // 완전한 경로 (/api/...)
  const targetUrl = `${BACKEND_URL}${apiPath}`;
  
  console.log(`🔄 Proxy: ${req.method} ${apiPath} → ${targetUrl}`);
  
  try {
    // 이미지 프록시의 경우 바이너리 데이터 처리
    const isImageProxy = apiPath.startsWith('/api/image-proxy');
    
    const response = await axios({
      method: req.method,
      url: targetUrl,
      data: req.body,
      headers: {
        'Content-Type': req.headers['content-type'] || 'application/json',
      },
      params: req.query,
      timeout: 120000, // 2 minutes
      responseType: isImageProxy ? 'arraybuffer' : 'json', // 이미지는 arraybuffer로 받기
    });
    
    console.log(`✅ Proxy success: ${response.status} for ${apiPath}`);
    
    // 이미지 프록시의 경우 바이너리 데이터 그대로 전달
    if (isImageProxy) {
      const contentType = response.headers['content-type'] || 'image/jpeg';
      res.set('Content-Type', contentType);
      res.set('Cache-Control', response.headers['cache-control'] || 'public, max-age=3600');
      res.set('Access-Control-Allow-Origin', '*');
      res.send(Buffer.from(response.data));
    } else {
      // 일반 API는 JSON으로 응답
      res.status(response.status).json(response.data);
    }
  } catch (error) {
    console.error(`❌ Proxy error for ${apiPath}:`, error.message);
    
    if (error.response) {
      // Backend responded with error
      res.status(error.response.status).json(error.response.data);
    } else {
      // Network or other error
      res.status(500).json({
        success: false,
        error: 'Backend proxy error',
        message: error.message
      });
    }
  }
});

// Serve static files from frontend/dist
app.use(express.static(path.join(__dirname, 'frontend/dist')));

// SPA fallback - serve index.html for all non-API routes
app.use((req, res) => {
  res.sendFile(path.join(__dirname, 'frontend/dist/index.html'));
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`);
  console.log(`✅ Shorts Creator Pro Frontend Server`);
  console.log(`━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`);
  console.log(`🌐 Frontend: http://localhost:${PORT}`);
  console.log(`🔗 API Proxy: /api/* → ${BACKEND_URL}/api/*`);
  console.log(`🖼️  Image Proxy: /api/image-proxy (binary support)`);
  console.log(`━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`);
});
