#!/bin/bash
# Add cache-busting version parameter
VERSION=$(date +%s)
sshpass -p '7009011226119' ssh -o StrictHostKeyChecking=no azamans@115.91.5.140 << SSHEOF
# Add version to HTML
echo '7009011226119' | sudo -S sed -i '/<head>/a\
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0">\
    <meta http-equiv="Pragma" content="no-cache">\
    <meta http-equiv="Expires" content="0">\
    <meta name="version" content="'$VERSION'">' /var/www/html/index.html

# Restart Nginx
echo '7009011226119' | sudo -S systemctl restart nginx

echo "✅ Cache busting added with version: $VERSION"
SSHEOF
