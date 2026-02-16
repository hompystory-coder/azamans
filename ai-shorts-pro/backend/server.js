require('dotenv').config();
const express = require('express');
const cors = require('cors');
const crawlerRoutes = require('./routes/crawlerRoutes');
const imageProxyRoutes = require('./routes/imageProxyRoutes');
const cacheRoutes = require('./routes/cacheRoutes');
const projectRoutes = require('./routes/projectRoutes');
const videoRoutes = require('./routes/videoRoutes');
const downloadRoutes = require('./routes/downloadRoutes');
const characterRoutes = require('./routes/characterRoutes');
const advancedRoutes = require('./routes/advancedRoutes');

const app = express();
const PORT = process.env.PORT || 5555;

// Middleware
app.use(cors());
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true, limit: '50mb' }));

// Routes
app.get('/api/health', (req, res) => {
  res.json({
    status: 'ok',
    timestamp: new Date().toISOString(),
    service: 'AI Shorts Pro Backend'
  });
});

app.use('/api/crawler', crawlerRoutes);
app.use('/api/image-proxy', imageProxyRoutes);
app.use('/api/cache', cacheRoutes);
app.use('/api/projects', projectRoutes);
app.use('/api/video', videoRoutes);
app.use('/api/download', downloadRoutes);
app.use('/api/character', characterRoutes);
app.use('/api/advanced', advancedRoutes);

// Static files for generated videos
app.use('/videos', express.static('/mnt/music-storage/shorts-videos/output'));
app.use('/videos', express.static('/mnt/music-storage/generated-shorts/videos'));
app.use('/generated', express.static('/mnt/music-storage/generated-shorts'));
app.use('/uploads', express.static('/mnt/music-storage/shorts-videos/uploads'));

// Error handling middleware
app.use((err, req, res, next) => {
  console.error('Error:', err);
  res.status(500).json({
    success: false,
    error: err.message || 'Internal server error'
  });
});

// Start server
app.listen(PORT, () => {
  console.log(`🚀 AI Shorts Pro Backend running on port ${PORT}`);
  console.log(`📡 Socket.io ready`);
});

module.exports = app;
