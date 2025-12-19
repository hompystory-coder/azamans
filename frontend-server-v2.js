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

// Manual API proxy - forward all /api/* requests to backend
app.use('/api/*', async (req, res) => {
  const apiPath = req.originalUrl; // 완전한 경로 (/api/...)
  const targetUrl = `${BACKEND_URL}${apiPath}`;
  
  console.log(`🔄 Proxy: ${req.method} ${apiPath} → ${targetUrl}`);
  
  try {
    const response = await axios({
      method: req.method,
      url: targetUrl,
      data: req.body,
      headers: {
        'Content-Type': req.headers['content-type'] || 'application/json',
      },
      params: req.query,
      timeout: 120000, // 2 minutes
    });
    
    console.log(`✅ Proxy success: ${response.status} for ${apiPath}`);
    res.status(response.status).json(response.data);
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
  console.log(`━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`);
});
