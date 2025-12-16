# 🚀 NeuralGrid Security Platform - Deployment Instructions

## 📦 Updated Files Ready for Production

### Modified Files:
1. ✅ `ddos-server-updated.js` → Deploy to `/var/www/ddos.neuralgrid.kr/server.js`

### Documentation Files (for reference):
2. ✅ `INSTALLATION_FLOW_FIX_SUMMARY.md` (comprehensive fix documentation)

## 🔧 Deployment Steps

### Step 1: Deploy Backend File
```bash
# Switch to root or use sudo
sudo su

# Copy updated backend
cp /home/azamans/webapp/ddos-server-updated.js /var/www/ddos.neuralgrid.kr/server.js

# Set correct permissions
chown www-data:www-data /var/www/ddos.neuralgrid.kr/server.js
chmod 644 /var/www/ddos.neuralgrid.kr/server.js

# Exit root
exit
```

### Step 2: Restart Service
```bash
# Restart the ddos-security service via PM2
pm2 restart ddos-security

# Check service status
pm2 status

# View logs to ensure no errors
pm2 logs ddos-security --lines 50
```

### Step 3: Verify Deployment
```bash
# Check if server is running
curl -I https://ddos.neuralgrid.kr/api/user/stats

# Should return HTTP 401 (requires auth) or 200 with token
# Any other response indicates an error
```

## 🧪 Testing Checklist

### Backend API Testing:
```bash
# Test 1: Check if API is responding
curl https://ddos.neuralgrid.kr/api/user/servers \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected: JSON array with server objects containing new fields:
# - name, ip, plan, status (online/offline), blockedIPs, blockedDomains
```

### Frontend Flow Testing:

#### Test Case 1: Website Protection Registration
1. **Navigate**: https://ddos.neuralgrid.kr/mypage.html
2. **Click**: "서버 추가" button
3. **Fill Form**: Select "홈페이지 보호", enter domains
4. **Submit**: Complete registration
5. **Expected**: Installation guide modal appears with JavaScript code
6. **Click**: "코드 복사" button
7. **Verify**: Code copied to clipboard
8. **Click**: "설치 완료" button
9. **Expected**: Redirect to My Page
10. **Verify**: Registered domains appear in server list

#### Test Case 2: Server Protection Registration
1. **Navigate**: https://ddos.neuralgrid.kr/mypage.html
2. **Click**: "서버 추가하기" button (if no servers)
3. **Fill Form**: Select "서버 보호", enter server IPs
4. **Submit**: Complete registration
5. **Expected**: Installation guide modal appears with Bash script
6. **Click**: "스크립트 복사" button
7. **Verify**: Script copied to clipboard
8. **Click**: "설치 완료" button
9. **Expected**: Redirect to My Page
10. **Verify**: Registered servers appear in server list with:
    - ✅ Server name/IP
    - ✅ Online status
    - ✅ Blocked IPs count
    - ✅ Blocked domains count
    - ✅ Plan type

#### Test Case 3: Server List Display
1. **Navigate**: https://ddos.neuralgrid.kr/mypage.html
2. **Expected**: Server cards displayed with:
   ```
   🖥️ [Server Name]
   상태: 온라인/오프라인
   IP: xxx.xxx.xxx.xxx
   도메인: example.com
   OS: Linux
   차단 IP: 42
   차단 도메인: 15
   플랜: 홈페이지 보호 / 서버 보호
   [관리] [로그] buttons
   ```

## 🐛 Troubleshooting

### Issue: Server not restarting
```bash
# Check PM2 status
pm2 list

# If process is errored
pm2 delete ddos-security
pm2 start /var/www/ddos.neuralgrid.kr/server.js --name ddos-security
```

### Issue: Permission denied
```bash
# Fix file ownership
sudo chown www-data:www-data /var/www/ddos.neuralgrid.kr/server.js

# Fix directory permissions
sudo chmod 755 /var/www/ddos.neuralgrid.kr
```

### Issue: API returning old data format
```bash
# Clear PM2 cache
pm2 flush

# Hard restart service
pm2 restart ddos-security --update-env

# Check logs for errors
pm2 logs ddos-security --err
```

### Issue: Servers not appearing on My Page
**Possible causes**:
1. Backend not deployed → Follow Step 1
2. Service not restarted → Follow Step 2
3. Authentication issue → Check token in localStorage
4. API returning wrong format → Check deployment was successful

**Debug**:
```javascript
// Open browser console on My Page
// Check network tab for /api/user/servers response

// Should see fields like:
// {
//   name: "example.com",
//   ip: "123.456.789.0",
//   plan: "website",
//   status: "online",
//   blockedIPs: 42,
//   blockedDomains: 15
// }
```

## 📋 Deployment Verification

Once deployed, verify the following:

### ✅ Checklist:
- [ ] Backend file deployed to `/var/www/ddos.neuralgrid.kr/server.js`
- [ ] PM2 service restarted successfully
- [ ] PM2 logs show no errors
- [ ] API endpoint `/api/user/servers` returns new field format
- [ ] My Page displays server cards correctly
- [ ] "Add Server" button redirects to registration page
- [ ] Registration modal shows installation guide
- [ ] Installation confirmation activates servers
- [ ] Activated servers appear on My Page

### 📊 Expected Outcome:
- **Status**: ✅ All systems operational
- **Features**: ✅ Complete registration-to-display flow working
- **UX**: ✅ Clear installation guidance with copy buttons
- **Data**: ✅ Server list displays with correct information

## 🔄 Rollback Plan

If deployment causes issues:

```bash
# Rollback to previous version
sudo cp /var/www/ddos.neuralgrid.kr/server.js.backup /var/www/ddos.neuralgrid.kr/server.js

# Restart service
pm2 restart ddos-security

# Verify rollback
pm2 logs ddos-security --lines 20
```

**Note**: Before deploying, create a backup:
```bash
sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup.$(date +%Y%m%d_%H%M%S)
```

## 📞 Support

If deployment issues persist:
- **Logs**: Check PM2 logs for detailed error messages
- **Network**: Use browser DevTools Network tab to inspect API responses
- **Console**: Check browser console for JavaScript errors
- **Documentation**: Refer to `INSTALLATION_FLOW_FIX_SUMMARY.md` for implementation details

---

**Last Updated**: 2025-12-16  
**Version**: 1.0  
**Status**: ✅ Ready for Production Deployment  
**Git Commit**: `1dad275`  
**Branch**: `genspark_ai_developer_clean`  
**PR**: https://github.com/hompystory-coder/azamans/pull/1
