#!/bin/bash
# Simple deployment script for neuralgrid main page
SOURCE="/home/azamans/webapp/neuralgrid-main-page.html"
DEST="/var/www/neuralgrid.kr/html/index.html"
BACKUP="${DEST}.backup_$(date +%Y%m%d_%H%M%S)"

echo "Creating backup: $BACKUP"
sudo cp "$DEST" "$BACKUP"

echo "Deploying new version..."
sudo cp "$SOURCE" "$DEST"
sudo chown www-data:www-data "$DEST"
sudo chmod 644 "$DEST"

echo "Deployment complete!"
ls -lh "$DEST"
