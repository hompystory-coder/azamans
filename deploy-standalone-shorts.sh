#!/bin/bash

echo "=== Deploying Standalone Shorts Market Server ==="

SERVER="115.91.5.140"
REMOTE_DIR="~/shorts-market"

# 1. Upload standalone server
echo "📤 Uploading standalone-server.js..."
scp standalone-server.js azamans@${SERVER}:${REMOTE_DIR}/

# 2. Upload database
echo "📤 Uploading database..."
scp shorts-market-backup.sqlite azamans@${SERVER}:${REMOTE_DIR}/

# 3. Stop wrangler processes
echo "🛑 Stopping wrangler processes..."
ssh azamans@${SERVER} "pkill -f 'wrangler pages dev' || true"

# 4. Install sqlite3 package if needed
echo "📦 Installing dependencies..."
ssh azamans@${SERVER} "cd ${REMOTE_DIR} && npm install sqlite3 --save"

# 5. Update PM2 configuration
echo "⚙️ Updating PM2 configuration..."
ssh azamans@${SERVER} "cd ${REMOTE_DIR} && cat > ecosystem-standalone.config.cjs << 'EOF'
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
"

# 6. Restart PM2 with new configuration
echo "🔄 Restarting PM2 process..."
ssh azamans@${SERVER} "cd ${REMOTE_DIR} && pm2 delete shorts-market || true && pm2 start ecosystem-standalone.config.cjs"

# 7. Test the service
echo "🧪 Testing service..."
sleep 3
ssh azamans@${SERVER} "curl -s http://localhost:3003/health | jq '.'"
echo ""
ssh azamans@${SERVER} "curl -s http://localhost:3003/api/config | jq '.'"
echo ""
ssh azamans@${SERVER} "curl -s http://localhost:3003/api/shorts | jq '.count, .data[0].title'"

echo ""
echo "✅ Deployment complete!"
echo "🌐 Service URL: https://market.neuralgrid.kr/"
