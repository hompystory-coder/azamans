#!/bin/bash
###############################################################################
# Fix permissions for NeuralGrid data directory
###############################################################################

echo "🔧 Fixing permissions for /var/lib/neuralgrid..."

# Create directory without sudo
if [ ! -d "/var/lib/neuralgrid" ]; then
    sudo mkdir -p /var/lib/neuralgrid
    echo "✅ Directory created"
fi

# Set ownership to current user and www-data group
sudo chown -R $USER:www-data /var/lib/neuralgrid
sudo chmod -R 775 /var/lib/neuralgrid

echo "✅ Permissions fixed"
echo ""
echo "Directory info:"
ls -la /var/lib/neuralgrid

echo ""
echo "✅ Done! Now restart the service:"
echo "   pm2 restart ddos-security"
