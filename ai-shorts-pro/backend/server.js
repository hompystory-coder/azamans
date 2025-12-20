require('dotenv').config();
const express = require('express');
const cors = require('cors');
const path = require('path');
const http = require('http');
const socketIO = require('socket.io');

// Routes
const projectRoutes = require('./routes/projects');
const characterRoutes = require('./routes/characters');
const voiceRoutes = require('./routes/voices');
const crawlerRoutes = require('./routes/crawler');
const generationRoutes = require('./routes/generation');
const assetsRoutes = require('./routes/assets');
const youtubeRoutes = require('./routes/youtube');

const app = express();
const server = http.createServer(app);
const io = socketIO(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Static files
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));
app.use('/generated', express.static(path.join(__dirname, 'generated')));

// Socket.io middleware
app.use((req, res, next) => {
  req.io = io;
  next();
});

// Routes
app.use('/api/projects', projectRoutes);
app.use('/api/characters', characterRoutes);
app.use('/api/voices', voiceRoutes);
app.use('/api/crawler', crawlerRoutes);
app.use('/api/generation', generationRoutes);
app.use('/api/assets', assetsRoutes);
app.use('/api/youtube', youtubeRoutes);

// Health check
app.get('/api/health', (req, res) => {
  res.json({ 
    status: 'ok', 
    timestamp: new Date().toISOString(),
    service: 'AI Shorts Pro Backend'
  });
});

// Error handler
app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ 
    error: 'Internal server error',
    message: err.message
  });
});

// Socket.io connection
io.on('connection', (socket) => {
  console.log('Client connected:', socket.id);
  
  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.id);
  });
});

const PORT = process.env.PORT || 5000;

server.listen(PORT, () => {
  console.log(`🚀 AI Shorts Pro Backend running on port ${PORT}`);
  console.log(`📡 Socket.io ready for real-time updates`);
});

module.exports = { app, io };
