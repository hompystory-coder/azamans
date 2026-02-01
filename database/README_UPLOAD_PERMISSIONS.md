# 업로드 디렉토리 권한 설정 가이드

## 🔒 권한 문제

CKEditor 이미지 업로드 시 **500 Internal Server Error**가 발생하는 경우, 대부분 **디렉토리 권한** 문제입니다.

### 문제 원인
- 웹 서버 사용자: `www-data`
- 디렉토리 소유자: `azamans` (또는 다른 사용자)
- 기본 권한: `755` (소유자만 쓰기 가능)

### 해결 방법

#### 1. 모든 업로드 디렉토리 권한 설정
```bash
cd /home/mvc
chmod -R 777 public/uploads/
```

#### 2. 개별 디렉토리 권한 설정
```bash
# 게시판 이미지
chmod -R 777 public/uploads/bbs/image/

# 페이지 이미지
chmod -R 777 public/uploads/page/image/

# 페이지 첨부파일
chmod -R 777 public/uploads/page/attach/

# 페이지 캐시
chmod -R 777 public/uploads/page/
```

#### 3. 소유자 변경 (권장)
```bash
# www-data로 소유자 변경
sudo chown -R www-data:www-data public/uploads/

# 권한 755로 설정
chmod -R 755 public/uploads/
```

---

## 📁 디렉토리 구조 및 권한

### 게시판 업로드
```
public/uploads/bbs/
├── image/          # 이미지 업로드 (777)
│   └── {년}/{월}/{일}/
│       └── *.jpg, *.png, ...
└── attach/         # 첨부파일 (미사용)
```

### 페이지 업로드
```
public/uploads/page/
├── image/          # 이미지 업로드 (777)
│   └── {년}/{월}/{일}/
│       └── *.jpg, *.png, ...
├── attach/         # 첨부파일 업로드 (777)
│   └── {년}/{월}/{일}/
│       └── *.pdf, *.zip, ...
└── {menu_id}.php   # 페이지 캐시 파일 (777)
```

---

## 🔍 권한 확인

### 현재 권한 확인
```bash
ls -la public/uploads/
ls -la public/uploads/bbs/image/
ls -la public/uploads/page/image/
ls -la public/uploads/page/attach/
```

### 예상 출력
```
drwxrwxrwx  3 www-data www-data 4096 Feb  2 03:00 bbs
drwxrwxrwx  5 www-data www-data 4096 Feb  2 03:00 page
```

---

## 🐛 디버깅

### 1. 브라우저 Console 확인
```
F12 → Console 탭

오류:
POST https://mvc.neuralgrid.kr/upload/bbs/image 500 (Internal Server Error)
```

### 2. Nginx 오류 로그
```bash
tail -f /var/log/nginx/error.log
```

### 3. PHP-FPM 오류 로그
```bash
tail -f /var/log/php8.3-fpm.log
```

### 4. 테스트 업로드
```bash
# 테스트 파일 생성
echo "test" > /tmp/test.txt

# 권한 테스트
touch public/uploads/bbs/image/test.txt
# 성공하면 권한 정상
# 실패하면 권한 문제

# 테스트 파일 삭제
rm public/uploads/bbs/image/test.txt
```

---

## 🚀 배포 시 주의사항

### Production 환경
```bash
# 보안을 위해 소유자 변경
sudo chown -R www-data:www-data public/uploads/

# 권한 최소화
chmod -R 755 public/uploads/
find public/uploads/ -type f -exec chmod 644 {} \;
```

### Development 환경
```bash
# 개발 편의를 위해 777 사용
chmod -R 777 public/uploads/
```

---

## 📝 체크리스트

- [ ] `public/uploads/bbs/image/` 권한 777
- [ ] `public/uploads/page/image/` 권한 777
- [ ] `public/uploads/page/attach/` 권한 777
- [ ] `public/uploads/page/` 권한 777
- [ ] 웹 서버 재시작 (필요 시)
- [ ] 브라우저 캐시 삭제 (Ctrl+F5)
- [ ] 테스트 업로드 성공

---

## 🎯 빠른 해결 방법

```bash
# 1단계: 모든 업로드 디렉토리 권한 변경
cd /home/mvc
chmod -R 777 public/uploads/

# 2단계: 브라우저 캐시 삭제
# Ctrl + F5 (강력 새로고침)

# 3단계: 이미지 업로드 테스트
# CKEditor에서 이미지 업로드 시도
```

---

## 📞 문제가 계속되는 경우

1. **웹 서버 재시작**
   ```bash
   sudo systemctl restart nginx
   sudo systemctl restart php8.3-fpm
   ```

2. **PHP 설정 확인**
   ```bash
   php -i | grep upload
   ```

3. **디스크 용량 확인**
   ```bash
   df -h
   ```

4. **SELinux 확인** (CentOS/RHEL)
   ```bash
   getenforce
   ```

---

## 🔗 관련 문서

- [CKEditor 이미지 업로드 설정](/home/mvc/editor.php)
- [Upload 컨트롤러](/home/mvc/application/controller/upload.php)
- [페이지 테이블 구조](/home/mvc/database/README_PAGE_TABLES.md)
