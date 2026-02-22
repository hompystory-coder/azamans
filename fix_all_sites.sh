#!/bin/bash

# 모든 사이트의 PHP 파일에 is_file() 체크 추가하는 스크립트
# 서버: 115.91.5.138

echo "=================================="
echo "전체 사이트 PHP 파일 수정 시작"
echo "=================================="

TIMESTAMP=$(date +%Y%m%d-%H%M%S)
BACKUP_DIR="/root/php_fixes_backup_$TIMESTAMP"

echo "백업 디렉토리: $BACKUP_DIR"
mkdir -p "$BACKUP_DIR"

# 수정할 파일 목록
FILES_TO_FIX=(
    "layouts/skinx-skin/_includes/_import.var.php"
    "layouts/skinx-skin/_pages/side1/side1.php"
    "layouts/skinx-skin/default-default.php"
    "application/views/news/view-mobile.php"
    "application/views/news/view-mobile-ck.php"
    "application/views/news/view-pc-ck.php"
    "modules/news/themes/_pc/default/default_rid.php"
    "layouts/skinx-skin/_includes/login.php"
    "index.php"
)

SITE_COUNT=0
FILE_COUNT=0
ERROR_COUNT=0

# 모든 public_html 디렉토리 찾기
for SITE_DIR in /home/*/public_html; do
    if [ ! -d "$SITE_DIR" ]; then
        continue
    fi
    
    SITE_NAME=$(echo "$SITE_DIR" | cut -d'/' -f3)
    SITE_COUNT=$((SITE_COUNT + 1))
    
    echo ""
    echo "[$SITE_COUNT] 처리 중: $SITE_NAME"
    
    for FILE_PATH in "${FILES_TO_FIX[@]}"; do
        FULL_PATH="$SITE_DIR/$FILE_PATH"
        
        if [ ! -f "$FULL_PATH" ]; then
            continue
        fi
        
        # 이미 is_file()이 있는지 확인
        if grep -q "is_file.*include" "$FULL_PATH" 2>/dev/null; then
            echo "  ✓ $FILE_PATH - 이미 수정됨"
            continue
        fi
        
        # 백업
        BACKUP_PATH="$BACKUP_DIR/$SITE_NAME"
        mkdir -p "$BACKUP_PATH/$(dirname $FILE_PATH)"
        cp "$FULL_PATH" "$BACKUP_PATH/$FILE_PATH" 2>/dev/null
        
        # 파일별 수정 적용
        case "$FILE_PATH" in
            *_import.var.php)
                # _import.var.php 수정
                if grep -q "include_once.*_news_sns.var.php" "$FULL_PATH"; then
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_sns.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_sns.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_search.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_search.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_send.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_send.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_perm.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_perm.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_share.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_share.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'.watermark.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'.watermark.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_shop.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_shop.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_blogpay.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_blogpay.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_coupang.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_coupang.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    FILE_COUNT=$((FILE_COUNT + 1))
                    echo "  ✅ $FILE_PATH - 수정 완료"
                fi
                ;;
            *side1.php)
                # side1.php 배너 코드 수정
                if grep -q "include.*banner.*code" "$FULL_PATH"; then
                    sed -i 's/include \$g\[\x27path_module\x27\].\x27news\/upload\/banner\/code\/\x27.\$_N\[\x27uid\x27\].\x27_code.php\x27/?> <?php $f = $g[\x27path_module\x27].\x27news\/upload\/banner\/code\/\x27.$_N[\x27uid\x27].\x27_code.php\x27; if(is_file($f)) include $f; ?>/g' "$FULL_PATH"
                    sed -i 's/<?php ?> //g' "$FULL_PATH"
                    sed -i 's/?>?>/?>  /g' "$FULL_PATH"
                    FILE_COUNT=$((FILE_COUNT + 1))
                    echo "  ✅ $FILE_PATH - 수정 완료"
                fi
                ;;
            *login.php)
                # login.php SNS 로그인 수정
                if grep -q "include.*snslogin/var" "$FULL_PATH"; then
                    sed -i "s|include './modules/snslogin/var/'.\$r.'.var.php';|\$f = './modules/snslogin/var/'.\$r.'.var.php'; if(is_file(\$f)) include \$f;|g" "$FULL_PATH"
                    FILE_COUNT=$((FILE_COUNT + 1))
                    echo "  ✅ $FILE_PATH - 수정 완료"
                fi
                ;;
            *view-mobile*.php|*view-pc*.php)
                # view 파일들 쿠팡 관련 수정
                if grep -q "_news_coupang.var.php" "$FULL_PATH"; then
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_news_coupang.var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_news_coupang.var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$fserverurl.\$r.'_coupang_product.php';|\$f = \$fserverurl.\$r.'_coupang_product.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    sed -i "s|include_once \$g\['path_var'\].'site/'.\$r.'/'.\$r.'_img_server_var.php';|\$f = \$g['path_var'].'site/'.\$r.'/'.\$r.'_img_server_var.php'; if(is_file(\$f)) include_once \$f;|g" "$FULL_PATH"
                    FILE_COUNT=$((FILE_COUNT + 1))
                    echo "  ✅ $FILE_PATH - 수정 완료"
                fi
                ;;
            *index.php)
                # index.php sitephp 수정
                if grep -q "include.*sitephp" "$FULL_PATH"; then
                    sed -i "s|include \$g\['path_var'\].'sitephp/'.\$_HS\['uid'\].'.php';|\$f = \$g['path_var'].'sitephp/'.\$_HS['uid'].'.php'; if(is_file(\$f)) include \$f;|g" "$FULL_PATH"
                    FILE_COUNT=$((FILE_COUNT + 1))
                    echo "  ✅ $FILE_PATH - 수정 완료"
                fi
                ;;
        esac
    done
done

echo ""
echo "=================================="
echo "수정 완료"
echo "=================================="
echo "처리된 사이트: $SITE_COUNT개"
echo "수정된 파일: $FILE_COUNT개"
echo "백업 위치: $BACKUP_DIR"
echo "=================================="
