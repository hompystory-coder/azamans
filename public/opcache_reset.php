<?php
// OPcache 완전 초기화
$result = [];

if (function_exists('opcache_reset')) {
    opcache_reset();
    $result[] = "✅ opcache_reset() executed";
}

if (function_exists('opcache_invalidate')) {
    // BASE_PATH 상수 로드 (index.php에서 정의됨)
    $basePath = dirname(__DIR__);
    opcache_invalidate($basePath . '/application/controller/admin.php', true);
    opcache_invalidate($basePath . '/application/libs/admin_index_func.php', true);
    $result[] = "✅ opcache_invalidate() executed for admin files";
}

if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    $result[] = "✅ APCu cache cleared";
}

// 현재 opcache 상태
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    $result[] = "📊 OPcache enabled: " . ($status['opcache_enabled'] ? 'YES' : 'NO');
    $result[] = "📊 Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'];
    $result[] = "📊 Hits: " . $status['opcache_statistics']['hits'];
    $result[] = "📊 Misses: " . $status['opcache_statistics']['misses'];
}

echo "<pre>";
echo "🔄 Cache Reset Results:\n\n";
echo implode("\n", $result);
echo "\n\n✅ Done! Cache cleared successfully.";
echo "</pre>";

// 5초 후 자동 삭제
sleep(1);
@unlink(__FILE__);
