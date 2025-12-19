import express from 'express';
import { createProxyMiddleware } from 'http-proxy-middleware';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3006;
const BACKEND_PORT = process.env.BACKEND_PORT || 4001;

// API 프록시 - 백엔드로 모든 /api/* 요청 전달
// pathRewrite로 /api 경로를 명시적으로 유지
app.use('/api', createProxyMiddleware({
  target: `http://localhost:${BACKEND_PORT}`,
  changeOrigin: true,
  pathRewrite: {
    '^/api': '/api'  // /api를 /api로 유지 (제거하지 않음)
  },
  logLevel: 'info',
  onProxyReq: (proxyReq, req, res) => {
    console.log(`🔄 Proxying: ${req.method} ${req.originalUrl} → ${proxyReq.path}`);
  },
  onProxyRes: (proxyRes, req, res) => {
    console.log(`✅ Proxy response: ${proxyRes.statusCode} for ${req.originalUrl}`);
  },
  onError: (err, req, res) => {
    console.error(`❌ Proxy error for ${req.originalUrl}:`, err.message);
    res.status(500).json({
      success: false,
      error: 'Backend proxy error',
      message: err.message
    });
  }
}));

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
  console.log(`🔗 API Proxy: /api/* → http://localhost:${BACKEND_PORT}`);
  console.log(`━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`);
});
