# 🚀 Manual Deployment Steps - DDoS Security Fix

## Current Issues
1. **401 Authentication Error** - Production server using old code
2. **Auth service working** - `curl` test confirms endpoint works
3. **Need deployment** - Updated code exists at `/home/azamans/webapp/ddos-server-updated.js`

## SSH Commands to Run (Copy-Paste)

```bash
# 1. SSH to server
ssh azamans@115.91.5.140
# Password: 7009011226119

# 2. Go to webapp directory
cd /home/azamans/webapp

# 3. Pull latest code
git pull origin genspark_ai_developer_clean

# 4. Deploy to production (needs sudo password: 7009011226119)
sudo cp ddos-server-updated.js /var/www/ddos.neuralgrid.kr/server.js

# 5. Fix permissions
sudo chown www-data:www-data /var/www/ddos.neuralgrid.kr/server.js

# 6. Restart PM2 service
pm2 restart ddos-security

# 7. Check logs for success
pm2 logs ddos-security --lines 50
```

## What to Look For in Logs

### ✅ Success Signs:
```
[Auth] 🔍 Verifying token...
[Auth] Calling: POST https://auth.neuralgrid.kr/api/auth/verify
[Auth] Response status: 200
[Auth] ✅ Token valid for user: xxx@xxx.com
[Auth] ✅ JWT authentication successful
```

### ❌ Error Signs:
```
Token verification failed: Unexpected token '<'
401 Unauthorized
```

## Browser Test After Deployment

1. Login: https://auth.neuralgrid.kr/
   - Email: aze7009011@gate.com
   - Check token in DevTools → Application → Cookies

2. Register: https://ddos.neuralgrid.kr/register.html
   - Company: 뉴럴그리드 테스트
   - Phone: 010-5137-0745
   - Domain: test.example.com

3. Check Network Tab:
   - `POST /api/servers/register-website`
   - Should return: **200 OK**
   - Should show: **Install Guide Modal**

## Troubleshooting

### If auth service has errors:
```bash
pm2 restart auth-service
pm2 logs auth-service --lines 50
```

### If still getting 401:
```bash
# Check production file
cat /var/www/ddos.neuralgrid.kr/server.js | grep -A 10 "verifyToken"

# Compare with updated file
cat /home/azamans/webapp/ddos-server-updated.js | grep -A 10 "verifyToken"
```

## Current File Differences

### Production (Old - Has Bug):
```javascript
async function verifyToken(token) {
    try {
        const response = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token })
        });
        const data = await response.json();
        return data.success ? data.user : null;
    } catch (error) {
        console.error('Token verification failed:', error.message);
        return null;
    }
}
```

### Updated (Has Debug Logs):
```javascript
async function verifyToken(token) {
    try {
        console.log('[Auth] 🔍 Verifying token...');
        console.log('[Auth] Calling: POST https://auth.neuralgrid.kr/api/auth/verify');
        
        const response = await fetch('https://auth.neuralgrid.kr/api/auth/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ token })
        });
        
        console.log('[Auth] Response status:', response.status);
        const data = await response.json();
        console.log('[Auth] Response data:', data);
        
        if (data.success) {
            console.log('[Auth] ✅ Token valid for user:', data.user?.email || 'unknown');
            return data.user;
        } else {
            console.log('[Auth] ❌ Token invalid:', data.error);
            return null;
        }
    } catch (error) {
        console.error('[Auth] ⚠️ Token verification failed:', error.message);
        return null;
    }
}
```

## Fix /var/lib/neuralgrid Permission Issue

```bash
# Create directory and fix permissions
sudo mkdir -p /var/lib/neuralgrid
sudo chown azamans:azamans /var/lib/neuralgrid
sudo chmod 755 /var/lib/neuralgrid
```

## Expected Result

After deployment:
1. ✅ No more 401 errors
2. ✅ Detailed auth logs appear
3. ✅ Registration completes successfully
4. ✅ Install guide modal appears
5. ✅ User redirected to My Page
6. ✅ Server appears in server list

---

**Last Updated**: 2025-12-16 09:20 KST
**Status**: 🔴 Needs Manual Deployment (sudo password required)
**Priority**: 🚨 URGENT - Production is broken
