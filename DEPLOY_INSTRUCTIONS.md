# 🚀 NeuralGrid Main Page Deployment Instructions

**Status**: Ready to Deploy  
**File Location**: `/tmp/neuralgrid-main-new.html`  
**Target**: `/var/www/neuralgrid.kr/html/index.html`

---

## 📋 Quick Deployment (1 Command)

새로운 메인 페이지가 준비되어 있습니다. 아래 명령어 **하나만 실행**하시면 즉시 배포됩니다:

```bash
sudo bash /tmp/deploy-main-page.sh
```

**실행 결과**:
- ✅ 기존 파일 자동 백업
- ✅ 새 버전 배포
- ✅ 권한 자동 설정

---

## 🔧 Manual Deployment (Step by Step)

만약 위 명령어가 작동하지 않는다면, 아래 단계를 따라주세요:

### **Step 1: Backup Current File**
```bash
sudo cp /var/www/neuralgrid.kr/html/index.html \
    /var/www/neuralgrid.kr/html/index.html.backup_$(date +%Y%m%d_%H%M%S)
```

### **Step 2: Deploy New Version**
```bash
sudo cp /tmp/neuralgrid-main-new.html /var/www/neuralgrid.kr/html/index.html
```

### **Step 3: Set Permissions**
```bash
sudo chown www-data:www-data /var/www/neuralgrid.kr/html/index.html
sudo chmod 644 /var/www/neuralgrid.kr/html/index.html
```

### **Step 4: Verify**
```bash
ls -lh /var/www/neuralgrid.kr/html/index.html
curl -I https://neuralgrid.kr
```

---

## 📊 What's Changed?

### **Removed**:
- ❌ Real-time Stats Section (CPU/Memory/Services/Uptime cards)
- ❌ Stats navigation link
- ❌ ~60 lines of Stats CSS

### **Added**:
- ✅ Unified Login Modal (Glassmorphism design)
- ✅ JWT-based SSO Authentication
- ✅ Enhanced Hero section ("한 번의 회원가입으로 모든 서비스 이용 가능")
- ✅ Auth Service integration
- ✅ ~170 lines of Auth modal CSS + JS

---

## 🔗 Services Accessible with SSO

한 번의 회원가입으로 다음 서비스 모두 접근 가능:

1. 🎬 **MediaFX Shorts** - https://mfx.neuralgrid.kr
2. 🎵 **NeuronStar Music** - https://music.neuralgrid.kr
3. 🛒 **BN Shop** - https://bn-shop.neuralgrid.kr
4. ⚙️ **N8N Automation** - https://n8n.neuralgrid.kr
5. 🖥️ **System Monitor** - https://monitor.neuralgrid.kr
6. 🔐 **Auth Service** - https://auth.neuralgrid.kr

---

## 🧪 Testing After Deployment

### **1. Visual Check**
브라우저에서 https://neuralgrid.kr 열기
- [ ] Stats 섹션이 제거되었는지 확인
- [ ] 서비스 카드가 정상 표시되는지 확인
- [ ] "무료 회원가입하기" 버튼 클릭 → 로그인 모달 오픈 확인

### **2. Login Modal Test**
- [ ] 로그인/회원가입 탭 전환 작동 확인
- [ ] 이메일/비밀번호 입력 필드 작동 확인
- [ ] 폼 제출 시 Auth API 호출 확인

### **3. Responsive Design**
- [ ] 모바일에서 모달 정상 표시 확인
- [ ] 서비스 그리드 반응형 확인

---

## ⚠️ Rollback (If Needed)

문제가 발생하면 백업 파일로 복원:

```bash
# 백업 파일 목록 확인
ls -lht /var/www/neuralgrid.kr/html/index.html.backup_*

# 가장 최근 백업으로 복원
sudo cp /var/www/neuralgrid.kr/html/index.html.backup_YYYYMMDD_HHMMSS \
    /var/www/neuralgrid.kr/html/index.html

# Nginx 재시작 (필요시)
sudo systemctl reload nginx
```

---

## 📝 File Locations

| File | Location | Size |
|------|----------|------|
| **New Version** | `/tmp/neuralgrid-main-new.html` | ~30 KB |
| **Current Live** | `/var/www/neuralgrid.kr/html/index.html` | ~34 KB |
| **Deployment Script** | `/tmp/deploy-main-page.sh` | - |
| **Local Source** | `/home/azamans/webapp/neuralgrid-main-page.html` | ~30 KB |

---

## 🎯 Quick Check

배포 후 즉시 확인:

```bash
# 파일 크기 확인 (30KB 정도여야 함)
ls -lh /var/www/neuralgrid.kr/html/index.html

# 내용 확인 (통합 로그인 포함 여부)
grep -i "auth-modal" /var/www/neuralgrid.kr/html/index.html && echo "✅ New version deployed!"

# 웹 접속 테스트
curl -I https://neuralgrid.kr | head -5
```

---

## 💡 Need Help?

**문제 발생 시**:
1. Nginx 로그 확인: `sudo tail -f /var/log/nginx/error.log`
2. 파일 권한 확인: `ls -l /var/www/neuralgrid.kr/html/index.html`
3. Nginx 재시작: `sudo systemctl restart nginx`

---

**Generated**: 2025-12-15  
**Ready for Deployment**: ✅ YES
