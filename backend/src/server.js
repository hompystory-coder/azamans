// YouTube Shorts Creator Pro - Backend Server
// 최신 ES Modules 사용

// Load environment variables FIRST
import './config/env.js';

import express from 'express';
import cors from 'cors';
import path from 'path';
import { fileURLToPath } from 'url';

// Routes
import settingsRouter from './routes/settings.js';
import crawlerRouter from './routes/crawler.js';
import scriptRouter from './routes/script.js';
import voiceRouter from './routes/voice.js';
import videoRouter from './routes/video.js';
import renderRouter from './routes/render.js';
import imageProxyRouter from './routes/imageProxy.js';
import advancedRouter from './routes/advanced.js';
import characterVideoRouter from './routes/character-video.js';

// Utils
import { ensureDirectories } from './utils/storage.js';

// ES Module __dirname 대체
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Initialize Express
const app = express();
const PORT = process.env.PORT || 4000;

// Middleware
app.use(cors({
  origin: process.env.CORS_ORIGIN || '*',
  credentials: true
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Static files (업로드된 파일 제공)
app.use('/uploads', express.static(process.env.UPLOAD_DIR || '/tmp/uploads'));
app.use('/outputs', express.static(process.env.OUTPUT_DIR || '/tmp/outputs'));
// 외장하드 비디오 저장소 (3.6TB)
app.use('/shorts-videos', express.static('/mnt/music-storage/shorts-videos'));

// Health check
app.get('/health', (req, res) => {
  res.json({
    status: 'ok',
    service: 'shorts-creator-pro-backend',
    version: '1.0.0',
    timestamp: new Date().toISOString()
  });
});

// API Routes
app.use('/api/settings', settingsRouter);
app.use('/api/crawler', crawlerRouter);
app.use('/api/script', scriptRouter);
app.use('/api/voice', voiceRouter);
app.use('/api/video', videoRouter);
app.use('/api/render', renderRouter);
app.use('/api/image-proxy', imageProxyRouter);
app.use('/api/advanced', advancedRouter);
app.use('/api/character-video', characterVideoRouter);

// 404 Handler
app.use((req, res) => {
  res.status(404).json({
    success: false,
    error: 'Endpoint not found',
    path: req.path
  });
});

// Error Handler
app.use((err, req, res, next) => {
  console.error('❌ Server Error:', err);
  res.status(500).json({
    success: false,
    error: err.message || 'Internal server error'
  });
});

// Start server
async function startServer() {
  try {
    // 필요한 디렉토리 생성
    await ensureDirectories();
    
    app.listen(PORT, '0.0.0.0', () => {
      console.log('🚀 Shorts Creator Pro Backend Server');
      console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
      console.log(`📡 Server running on port ${PORT}`);
      console.log(`🌐 Health check: http://localhost:${PORT}/health`);
      console.log(`🔧 Environment: ${process.env.NODE_ENV || 'development'}`);
      console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
      console.log('📋 Available endpoints:');
      console.log('   POST /api/settings/save');
      console.log('   GET  /api/settings/list');
      console.log('   POST /api/crawler/fetch');
      console.log('   GET  /api/image-proxy?url=IMAGE_URL');
      console.log('   POST /api/script/generate');
      console.log('   POST /api/voice/generate');
      console.log('   POST /api/voice/preview');
      console.log('   POST /api/video/generate');
      console.log('   POST /api/character-video/generate');
      console.log('   POST /api/character-video/test');
      console.log('   GET  /api/character-video/status/:taskId');
      console.log('   POST /api/render/final');
      console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    });
  } catch (error) {
    console.error('❌ Failed to start server:', error);
    process.exit(1);
  }
}

startServer();
