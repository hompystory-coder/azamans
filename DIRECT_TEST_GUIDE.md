# 🔧 Direct Browser Test Guide

## 🎯 Goal
Test the complete authentication flow and identify exactly where it breaks.

---

## 📋 Test Steps

### **Step 1: Access Test Page**
```
https://ddos.neuralgrid.kr/test-flow.html
```

This page will show you:
- ✅ Current token status in localStorage
- ✅ Current token status in sessionStorage  
- ✅ Current token status in cookies
- ✅ getAuthToken() function result

---

### **Step 2: Clear Everything (if needed)**
Click the **"🗑️ Clear All Storage"** button

---

### **Step 3: Test Login**
Click the **"🔑 Test Login"** button

**Expected Result:**
```
✅ Login successful! Token length: 183
✅ Token saved to localStorage and cookie
✅ Found valid token: 183 characters
```

**If you see an error:**
- Screenshot the console log
- Report the exact error message

---

### **Step 4: Test DDoS Registration**
Click the **"📝 Test DDoS Register"** button

**Expected Result:**
```
✅ Using token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
Register Response: 200 OK
✅ Registration successful!
Order ID: ORD-1734389946628-VCHQZEPN9
API Key: NGS_66E80F0982A2A93AC3C26EDCBB58D5FD
Install Code length: 450
```

**If you see 401 Unauthorized:**
- Check if the token is present
- Check token length (should be 150+ characters)

---

### **Step 5: Test on Actual Registration Page**

#### 5.1 Open Actual Page
```
https://ddos.neuralgrid.kr/register.html
```

#### 5.2 Open Browser Console (F12)

#### 5.3 Check Token Status
Paste this code in console:
```javascript
const token = localStorage.getItem('neuralgrid_token');
console.log('Token length:', token ? token.length : 'null');
console.log('Token value:', token ? token.substring(0, 50) + '...' : 'null');
```

#### 5.4 Click "🌐 홈페이지 보호 신청"

**Expected Result:**
- Modal opens
- Form appears
- No alert saying "로그인이 필요합니다"

**If alert appears:**
1. Check console for `[Token] No valid token found`
2. Run this diagnostic:
```javascript
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

console.log('localStorage:', localStorage.getItem('neuralgrid_token'));
console.log('sessionStorage:', sessionStorage.getItem('neuralgrid_token'));
console.log('cookie:', getCookie('neuralgrid_token'));
console.log('all cookies:', document.cookie);
```

---

## 🔍 Common Issues & Solutions

### Issue 1: Token is "undefined" string
**Solution:** Clear all storage and re-login
```javascript
localStorage.clear();
sessionStorage.clear();
window.location.href = 'https://auth.neuralgrid.kr/';
```

### Issue 2: Token is null after login
**Cause:** Backend not returning token in correct format  
**Solution:** Check Auth service backend response structure

### Issue 3: Cookie not accessible on ddos.neuralgrid.kr
**Cause:** Domain mismatch or Secure flag issue  
**Solution:** Check cookie settings in browser DevTools → Application → Cookies

### Issue 4: "로그인이 필요합니다" alert appears even with valid token
**Cause:** getAuthToken() returns null despite token existing  
**Solution:** Debug getAuthToken() function with console logs

---

## 📊 Expected Flow

```
1. User opens https://ddos.neuralgrid.kr/register.html
   ↓
2. Clicks "홈페이지 보호 신청" button
   ↓
3. openWebsiteModal() is called
   ↓
4. checkAuth(callback) is called
   ↓
5. getAuthToken() checks: localStorage → sessionStorage → cookies
   ↓
6. If token found (150+ chars): callback() → modal opens ✅
   If no token: alert + redirect to auth.neuralgrid.kr ❌
```

---

## 🎯 Success Criteria

**✅ WORKING:**
- Test page shows valid token (150+ chars)
- "Test Login" button works (200 OK)
- "Test DDoS Register" button works (200 OK)
- Actual register page opens modal without alert
- Form submission succeeds (200 OK)
- Install guide modal appears with JavaScript code

**❌ BROKEN:**
- Alert: "로그인이 필요합니다"
- 401 Unauthorized on API calls
- Token is "undefined" or null
- Modal doesn't open

---

## 📞 Report Format

Please test and report back with:

```
**Test Results:**
1. Test Page Status: [✅ Working / ❌ Broken]
2. Token Found: [Yes / No]
3. Token Length: [XXX characters]
4. Test Login: [✅ 200 OK / ❌ Error]
5. Test Register: [✅ 200 OK / ❌ 401]
6. Actual Page Modal: [✅ Opens / ❌ Alert appears]
7. Console Errors: [None / Screenshot attached]

**Screenshots:**
- [ ] Test page screenshot
- [ ] Console log screenshot
- [ ] Network tab screenshot (if 401 error)
```

---

## 🚀 Quick Access URLs

- **Test Page:** https://ddos.neuralgrid.kr/test-flow.html
- **Auth Login:** https://auth.neuralgrid.kr/
- **Register Page:** https://ddos.neuralgrid.kr/register.html
- **Token Debug:** https://ddos.neuralgrid.kr/check-auth.html

---

## 📝 Notes

- Use **Ctrl + Shift + R** to force refresh
- Test in **Incognito mode** if issues persist
- Clear cache between tests
- Check Network tab for API responses

---

**Status:** Ready for testing  
**Priority:** HIGH  
**ETA:** 5 minutes to complete all tests
