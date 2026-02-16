<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared successfully!";
} else {
    echo "⚠️ OPcache not available";
}

if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "\n✅ APCu cache cleared!";
}

echo "\n✅ Cache clear completed!";
@unlink(__FILE__); // 자기 자신 삭제
