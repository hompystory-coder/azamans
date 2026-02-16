# RSS 피드 접근 방법

## ⚠️ 현재 상황
`.htaccess` 규칙이 Nginx 환경에서 작동하지 않습니다.
Apache에서는 정상 작동하지만, 현재 서버는 Nginx를 사용하고 있습니다.

## ✅ 해결 방법

### 방법 1: 직접 URL 사용 (현재 작동)
```
https://mvc.neuralgrid.kr/index.php?url=rss/index
https://mvc.neuralgrid.kr/index.php?url=rss/bbs
https://mvc.neuralgrid.kr/index.php?url=rss/news
```

### 방법 2: Nginx 설정 추가 (권장)
Nginx 설정 파일에 다음 규칙을 추가하세요:

```nginx
# RSS 피드 라우팅
location ~ ^/rss_(index|bbs|news)\.xml$ {
    rewrite ^/rss_index\.xml$ /index.php?url=rss/index last;
    rewrite ^/rss_bbs\.xml$ /index.php?url=rss/bbs last;
    rewrite ^/rss_news\.xml$ /index.php?url=rss/news last;
}

# Sitemap 라우팅
location ~ ^/sitemap_(index|bbs|news)\.xml$ {
    rewrite ^/sitemap_index\.xml$ /index.php?url=sitemap/index last;
    rewrite ^/sitemap_bbs\.xml$ /index.php?url=sitemap/bbs last;
    rewrite ^/sitemap_news\.xml$ /index.php?url=sitemap/news last;
}
```

### 방법 3: 심볼릭 링크 (임시 해결)
```bash
cd /home/mvc/public
ln -s ../index.php rss_index.xml
ln -s ../index.php rss_bbs.xml
ln -s ../index.php rss_news.xml
```

그 후 `.htaccess`에 다음 추가:
```apache
RewriteCond %{REQUEST_URI} ^/rss_.*\.xml$
RewriteCond %{QUERY_STRING} ^$
RewriteRule ^rss_(index|bbs|news)\.xml$ /index.php?url=rss/$1 [L,QSA]
```

## 🎯 추천 방법
**Nginx 설정 추가 (방법 2)** 를 사용하는 것이 가장 깔끔합니다.

서버 관리자에게 Nginx 설정을 요청하거나,
`/etc/nginx/sites-available/mvc.neuralgrid.kr` 파일을 직접 수정하세요.

## 📝 Nginx 완전한 설정 예시
```nginx
server {
    listen 80;
    server_name mvc.neuralgrid.kr;
    root /home/mvc/public;
    index index.php index.html;

    # RSS 피드
    location ~ ^/rss_(index|bbs|news)\.xml$ {
        try_files $uri /index.php?url=rss/$1;
    }

    # Sitemap
    location ~ ^/sitemap_(index|bbs|news)\.xml$ {
        try_files $uri /index.php?url=sitemap/$1;
    }

    # PHP 처리
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    # 기본 라우팅
    location / {
        try_files $uri $uri/ /index.php?url=$uri&$args;
    }
}
```

## ✅ 테스트
설정 후 다음 명령으로 테스트:
```bash
curl -s "https://mvc.neuralgrid.kr/rss_index.xml" | head -n 20
```

XML 출력이 나오면 성공!
