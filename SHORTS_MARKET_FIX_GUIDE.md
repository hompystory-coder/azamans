# Shorts Market Database Fix Guide

## 🔴 Problem
The Shorts Market at `https://market.neuralgrid.kr/` is showing **empty data** and API errors:
- API returns: `{"success":false,"error":"쇼츠 목록 조회 중 오류가 발생했습니다."}`
- Root cause: Wrangler D1 database has no tables (`D1_ERROR: no such table: shorts`)
- API keys are configured but data is not accessible

## ✅ Solution
Replace Wrangler-based server with **standalone Node.js server** that uses the backed up SQLite database directly.

## 📊 Data Verification
Backed up database (`shorts-market-backup.sqlite`) contains:
- ✅ **5 users**
- ✅ **42 shorts**  
- ✅ **3 creators**
- ✅ **0 purchases**
- ✅ **4 click_logs**

All API keys are preserved:
- `COUPANG_ACCESS_KEY`: `c70d5581-434b-4223-9c81-f72641545958`
- `COUPANG_SECRET_KEY`: `115b6ad08b30eeba54a624f2ed94ca3f0f18005d`
- `COUPANG_PARTNER_ID`: `AF8150630`
- `JWT_SECRET`: `your_jwt_secret_here_ESlISrPC33IMEwsYuVQq703GmaU4eQ9wP9cmMytkMzw=`

---

## 🚀 Manual Deployment Steps

### Step 1: Upload Files to Server (115.91.5.140)

```bash
# On your local machine, upload these files to the server:
scp standalone-server.js azamans@115.91.5.140:~/shorts-market/
scp shorts-market-backup.sqlite azamans@115.91.5.140:~/shorts-market/
```

### Step 2: SSH into Server

```bash
ssh azamans@115.91.5.140
cd ~/shorts-market
```

### Step 3: Stop Wrangler Processes

```bash
# Kill all wrangler processes
pkill -f "wrangler pages dev"

# Verify they're stopped
ps aux | grep wrangler
```

### Step 4: Install Dependencies

```bash
# Install sqlite3 node module
npm install sqlite3 --save

# Verify installation
npm list sqlite3
```

### Step 5: Create PM2 Configuration

```bash
# Create new PM2 config file
cat > ecosystem-standalone.config.cjs << 'EOF'
module.exports = {
  apps: [{
    name: 'shorts-market',
    script: 'standalone-server.js',
    cwd: '/home/azamans/shorts-market',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    env: {
      NODE_ENV: 'production',
      PORT: 3003,
      COUPANG_ACCESS_KEY: 'c70d5581-434b-4223-9c81-f72641545958',
      COUPANG_SECRET_KEY: '115b6ad08b30eeba54a624f2ed94ca3f0f18005d',
      COUPANG_PARTNER_ID: 'AF8150630',
      JWT_SECRET: 'your_jwt_secret_here_ESlISrPC33IMEwsYuVQq703GmaU4eQ9wP9cmMytkMzw=',
      BASE_URL: 'https://market.neuralgrid.kr'
    },
    error_file: './logs/error.log',
    out_file: './logs/out.log',
    log_date_format: 'YYYY-MM-DD HH:mm:ss Z'
  }]
};
EOF
```

### Step 6: Restart PM2 Service

```bash
# Delete old PM2 process
pm2 delete shorts-market

# Start with new configuration
pm2 start ecosystem-standalone.config.cjs

# Save PM2 configuration
pm2 save

# Check status
pm2 status
pm2 logs shorts-market --lines 20
```

### Step 7: Test the Service

```bash
# Test health endpoint
curl http://localhost:3003/health | jq '.'

# Test configuration endpoint (shows API keys)
curl http://localhost:3003/api/config | jq '.'

# Test shorts API (should return data)
curl http://localhost:3003/api/shorts | jq '.success, .count'

# Test public URL
curl https://market.neuralgrid.kr/api/shorts | jq '.success, .count'
```

---

## 📋 Verification Checklist

After deployment, verify these:

- [ ] PM2 process `shorts-market` is **online**
- [ ] Health endpoint returns `{"status": "ok"}`
- [ ] Config endpoint shows **all API keys**
- [ ] Shorts API returns `{"success": true, "count": 42}`
- [ ] Public URL `https://market.neuralgrid.kr/` is accessible
- [ ] No errors in `pm2 logs shorts-market`

---

## 🔍 Troubleshooting

### Issue: "Cannot find module 'sqlite3'"
```bash
cd ~/shorts-market
npm install sqlite3 --save
pm2 restart shorts-market
```

### Issue: "Database file not found"
```bash
# Check if database file exists
ls -lh ~/shorts-market/shorts-market-backup.sqlite

# If missing, re-upload from local machine
scp shorts-market-backup.sqlite azamans@115.91.5.140:~/shorts-market/
```

### Issue: PM2 process keeps restarting
```bash
# Check error logs
pm2 logs shorts-market --err --lines 50

# Check if port 3003 is already in use
netstat -tlnp | grep 3003

# Kill process on port 3003 if needed
fuser -k 3003/tcp
```

### Issue: API returns empty data
```bash
# Verify database content
sqlite3 ~/shorts-market/shorts-market-backup.sqlite "SELECT COUNT(*) FROM shorts;"

# Should return 42
```

---

## 📊 Expected Results

After successful deployment:

### Health Check
```json
{
  "status": "ok",
  "timestamp": "2025-12-15T..."
}
```

### Configuration
```json
{
  "success": true,
  "config": {
    "COUPANG_ACCESS_KEY": "c70d5581-434b-4223-9c81-f72641545958",
    "COUPANG_SECRET_KEY": "115b6ad08b...",
    "COUPANG_PARTNER_ID": "AF8150630",
    "YOUTUBE_API_KEY": "NOT_SET",
    "JWT_SECRET": "CONFIGURED",
    "DATABASE": ".../shorts-market-backup.sqlite"
  }
}
```

### Shorts Data
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "💰 가성비 최고 무선이어폰 추천!",
      "youtube_video_id": "dQw4w9WgXcQ",
      "coupang_product_url": "https://www.coupang.com/vp/products/123456",
      ...
    }
  ],
  "count": 42
}
```

---

## 🎯 What Changed

| Before | After |
|--------|-------|
| Wrangler D1 database (empty) | SQLite database (148KB, full data) |
| Cloudflare Workers runtime | Node.js + Express server |
| `dist/_worker.js` | `standalone-server.js` |
| D1 migrations needed | Direct database access |
| Database errors | All data accessible |

---

## 🔗 Key Files

- **Server**: `~/shorts-market/standalone-server.js`
- **Database**: `~/shorts-market/shorts-market-backup.sqlite`
- **PM2 Config**: `~/shorts-market/ecosystem-standalone.config.cjs`
- **Logs**: `~/shorts-market/logs/`

---

## 📞 Support

If issues persist:
1. Check PM2 logs: `pm2 logs shorts-market --lines 100`
2. Check Nginx logs: `sudo tail -f /var/log/nginx/error.log`
3. Verify database: `sqlite3 ~/shorts-market/shorts-market-backup.sqlite ".tables"`
4. Test locally: `curl http://localhost:3003/api/shorts`

---

**✅ After following these steps, all data and API keys should be visible at `https://market.neuralgrid.kr/`**
