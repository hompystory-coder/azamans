# 🎬 AI Shorts Pro - Quick Deployment Guide

## Current Status ✅

- ✅ **Backend**: Running on port 5555 (healthy)
- ✅ **Frontend**: Built and ready in `ai-shorts-pro/frontend/dist`
- ✅ **Nginx Config**: Created in `/etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf`
- ⏳ **DNS**: Needs to be enabled via nginx symlink

---

## 🚀 One-Command Deployment

### Step 1: Enable Nginx Configuration
```bash
sudo bash /home/azamans/webapp/enable-ai-shorts.sh
```

This will:
- Enable the site in nginx
- Test configuration
- Reload nginx
- Your site will be live at **https://ai-shorts.neuralgrid.kr**

### Step 2 (Optional): Setup PM2 for Auto-Restart
```bash
bash /home/azamans/webapp/setup-pm2-ai-shorts.sh
```

This will:
- Stop current node process
- Start with PM2 for auto-restart and monitoring
- Save PM2 configuration

---

## 🌐 Access Points

Once Step 1 is complete:

- **Frontend**: https://ai-shorts.neuralgrid.kr
- **API Health**: https://ai-shorts.neuralgrid.kr/api/health
- **Backend**: Port 5555 (proxied through nginx)

---

## 🔍 Verification

After running the script:

```bash
# Check nginx status
systemctl status nginx

# Check backend
curl https://ai-shorts.neuralgrid.kr/api/health

# Check frontend
curl -I https://ai-shorts.neuralgrid.kr

# View backend logs
tail -f /home/azamans/webapp/ai-shorts-pro/backend/server.log

# If using PM2
pm2 status
pm2 logs ai-shorts-pro
```

---

## 📊 System Architecture

```
Client Browser
      ↓
https://ai-shorts.neuralgrid.kr (Port 443)
      ↓
Nginx Reverse Proxy
      ↓
├─→ Frontend: React App (Static Files from /dist)
└─→ Backend API: Express.js (Port 5555)
    └─→ Services:
        ├─ AI Service (Character Images, Videos)
        ├─ FFmpeg Service (Video Rendering)
        ├─ Voice Service (TTS with Google/Minimax/ElevenLabs)
        └─ YouTube Metadata Generator
```

---

## 🔧 Manual Steps (If needed)

If automatic script doesn't work:

### Enable Nginx Site Manually
```bash
# Create symlink
sudo ln -sf /etc/nginx/sites-available/ai-shorts.neuralgrid.kr.conf /etc/nginx/sites-enabled/

# Test config
sudo nginx -t

# Reload nginx
sudo systemctl reload nginx
```

### Restart Backend Manually
```bash
cd /home/azamans/webapp/ai-shorts-pro/backend

# Stop existing process
pkill -f "node server.js"

# Start backend
nohup node server.js > server.log 2>&1 &

# Check health
curl http://localhost:5555/api/health
```

---

## 📋 Project Details

- **Domain**: ai-shorts.neuralgrid.kr
- **Backend Port**: 5555
- **Frontend**: Built with React + Vite + Tailwind CSS
- **Backend**: Express.js + Socket.io + Redis
- **AI Models**: 
  - Nano Banana Pro (Character Images)
  - Minimax Hailuo 2.3 (Video Animation)
  - Google Gemini TTS (Voice)
  - ElevenLabs Music (BGM)
- **Video Processing**: FFmpeg
- **Features**:
  - 10 Character Presets
  - 8 AI Voices
  - Auto Script Generation
  - YouTube Metadata Generator
  - Cost: ~$0.30/video (45% savings)
  - Time: ~25 minutes/video

---

## 🆘 Troubleshooting

### Backend not responding
```bash
# Check if process is running
ps aux | grep "node server.js"

# Check logs
tail -100 /home/azamans/webapp/ai-shorts-pro/backend/server.log

# Restart
cd /home/azamans/webapp/ai-shorts-pro/backend
pkill -f "node server.js"
nohup node server.js > server.log 2>&1 &
```

### Nginx issues
```bash
# Check nginx error log
sudo tail -100 /var/log/nginx/ai-shorts.neuralgrid.kr.error.log

# Test config
sudo nginx -t

# Restart nginx
sudo systemctl restart nginx
```

### Port already in use
```bash
# Find process using port 5555
lsof -i :5555

# Kill process
kill -9 <PID>

# Or use pkill
pkill -f "node server.js"
```

---

## 📝 Environment Configuration

The backend uses `/home/azamans/webapp/ai-shorts-pro/backend/.env`:

```env
# Required API Keys
GENSPARK_API_KEY=your_api_key_here

# Optional (for future features)
REDIS_URL=redis://localhost:6379
OPENAI_API_KEY=your_openai_key
ELEVENLABS_API_KEY=your_elevenlabs_key
```

---

## 🔄 Updates & Maintenance

### Update code
```bash
cd /home/azamans/webapp
git pull origin genspark_ai_developer

cd ai-shorts-pro/frontend
npm run build

# Restart backend
pm2 restart ai-shorts-pro
# Or manually: pkill -f "node server.js" && cd backend && nohup node server.js > server.log 2>&1 &
```

### View logs
```bash
# Nginx access log
sudo tail -f /var/log/nginx/ai-shorts.neuralgrid.kr.access.log

# Backend log
tail -f /home/azamans/webapp/ai-shorts-pro/backend/server.log

# PM2 logs (if using PM2)
pm2 logs ai-shorts-pro
```

---

## ✅ Deployment Checklist

- [x] Backend dependencies installed (`npm install`)
- [x] Frontend dependencies installed (`npm install`)
- [x] Frontend built (`npm run build`)
- [x] Backend running on port 5555
- [x] Nginx config created
- [ ] **Nginx config enabled** ← Run `sudo bash enable-ai-shorts.sh`
- [ ] PM2 setup (optional)
- [ ] Test site access
- [ ] Verify API endpoints
- [ ] Monitor logs

---

**Ready to deploy!** Just run:

```bash
sudo bash /home/azamans/webapp/enable-ai-shorts.sh
```

Then visit: **https://ai-shorts.neuralgrid.kr** 🎉
