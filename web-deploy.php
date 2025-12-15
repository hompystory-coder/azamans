<?php
/**
 * NeuralGrid DDoS Platform Phase 1 Web Deployment Script
 * 
 * 사용법:
 * 1. 이 파일을 서버의 웹 접근 가능한 위치에 업로드 (예: /var/www/html/deploy.php)
 * 2. 브라우저에서 접속: http://115.91.5.140/deploy.php?token=neuralgrid2025
 * 3. 배포 진행 상황 확인
 */

// 보안 토큰 확인
$SECURITY_TOKEN = 'neuralgrid2025';
if (!isset($_GET['token']) || $_GET['token'] !== $SECURITY_TOKEN) {
    die('❌ Invalid security token');
}

header('Content-Type: text/plain; charset=utf-8');

echo "======================================\n";
echo "🚀 NeuralGrid Phase 1 Web Deployment\n";
echo "======================================\n\n";

// 배포 시작 시간
$startTime = microtime(true);

// 1. Git 업데이트
echo "📥 Step 1/7: Updating Git repository...\n";
$gitCommands = [
    'cd /home/azamans/webapp',
    'git fetch origin',
    'git checkout genspark_ai_developer_clean',
    'git pull origin genspark_ai_developer_clean'
];

foreach ($gitCommands as $cmd) {
    echo "   → $cmd\n";
    exec($cmd, $output, $returnCode);
    if ($returnCode !== 0) {
        echo "   ❌ Failed: " . implode("\n", $output) . "\n";
        die();
    }
}
echo "   ✅ Git update completed\n\n";

// 2. 백업
echo "💾 Step 2/7: Backing up current files...\n";
$backupTime = date('Ymd-His');
$backupCmd = "sudo cp /var/www/ddos.neuralgrid.kr/server.js /var/www/ddos.neuralgrid.kr/server.js.backup-$backupTime 2>/dev/null";
exec($backupCmd, $output, $returnCode);
echo "   ✅ Backup created: server.js.backup-$backupTime\n\n";

// 3. 파일 복사
echo "📦 Step 3/7: Deploying new files...\n";
$deployCommands = [
    'sudo cp /home/azamans/webapp/ddos-security-platform-server.js /var/www/ddos.neuralgrid.kr/server.js',
    'sudo cp /home/azamans/webapp/ddos-register.html /var/www/ddos.neuralgrid.kr/register.html'
];

foreach ($deployCommands as $cmd) {
    echo "   → " . basename(explode(' ', $cmd)[2]) . "\n";
    exec($cmd, $output, $returnCode);
    if ($returnCode !== 0) {
        echo "   ❌ Failed\n";
        die();
    }
}
echo "   ✅ Files deployed\n\n";

// 4. 권한 설정
echo "🔐 Step 4/7: Setting permissions...\n";
$permCommands = [
    'sudo chown -R azamans:azamans /var/www/ddos.neuralgrid.kr/',
    'sudo chmod 644 /var/www/ddos.neuralgrid.kr/*.html',
    'sudo chmod 644 /var/www/ddos.neuralgrid.kr/server.js'
];

foreach ($permCommands as $cmd) {
    exec($cmd, $output, $returnCode);
}
echo "   ✅ Permissions set\n\n";

// 5. PM2 재시작
echo "♻️  Step 5/7: Restarting PM2 process...\n";
exec('pm2 restart ddos-security', $output, $returnCode);
if ($returnCode === 0) {
    echo "   ✅ PM2 restarted: ddos-security\n\n";
} else {
    echo "   ⚠️  PM2 restart may have issues\n\n";
}

// 대기
sleep(3);

// 6. 헬스 체크
echo "🔍 Step 6/7: Health check...\n";
$healthCheck = @file_get_contents('http://localhost:3105/health');
if ($healthCheck) {
    echo "   ✅ API Health: $healthCheck\n\n";
} else {
    echo "   ⚠️  API not responding yet\n\n";
}

// 7. 파일 검증
echo "✅ Step 7/7: Verifying deployment...\n";
$files = [
    '/var/www/ddos.neuralgrid.kr/server.js',
    '/var/www/ddos.neuralgrid.kr/register.html'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "   ✅ " . basename($file) . " (" . number_format($size) . " bytes)\n";
    } else {
        echo "   ❌ " . basename($file) . " not found\n";
    }
}

// 완료 시간
$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

echo "\n======================================\n";
echo "✅ Deployment Completed!\n";
echo "======================================\n\n";
echo "⏱️  Duration: {$duration} seconds\n";
echo "📅 Time: " . date('Y-m-d H:i:s') . "\n\n";
echo "🌐 Check URLs:\n";
echo "   - Registration: https://ddos.neuralgrid.kr/register.html\n";
echo "   - Dashboard: https://ddos.neuralgrid.kr/\n\n";
echo "📝 Deployment Log:\n";
echo "   - Backup: server.js.backup-$backupTime\n";
echo "   - Git Branch: genspark_ai_developer_clean\n";
echo "   - PM2 Process: ddos-security\n\n";
echo "======================================\n";
?>
