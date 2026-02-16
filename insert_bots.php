<?php
/**
 * 봇 데이터 DB 삽입 스크립트
 */

// Bootstrap
define('BASE_PATH', __DIR__);
define('APP_PATH', BASE_PATH . '/application');

require_once APP_PATH . '/config/_env.func.php';
loadEnv(BASE_PATH . '/.env');

require_once APP_PATH . '/config/_db_func.php';

// 일반 쿼리 실행 헬퍼 함수
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("executeQuery Error: " . $e->getMessage());
        throw $e;
    }
}

echo "Starting bot data insertion...\n\n";

// 봇 목록 로드
$botListFile = __DIR__ . '/application/config/bot_list.php';
$botData = include $botListFile;

$searchBots = $botData['searchBots'];
$aiBots = $botData['aiBots'];

$successCount = 0;
$errorCount = 0;

// 검색엔진 봇 삽입
echo "Inserting search engine bots...\n";
foreach ($searchBots as $bot) {
    $sql = "INSERT INTO bot_config (user_agent, bot_name, description, category, bot_type, is_allowed) 
            VALUES (?, ?, ?, ?, 'search', 0)
            ON DUPLICATE KEY UPDATE 
                bot_name = VALUES(bot_name),
                description = VALUES(description),
                category = VALUES(category),
                bot_type = VALUES(bot_type)";
    
    try {
        executeQuery($sql, [
            $bot['user_agent'],
            $bot['name'],
            $bot['description'],
            $bot['category']
        ]);
        $successCount++;
        echo ".";
    } catch (Exception $e) {
        echo "\nError inserting {$bot['name']}: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\nInserted {$successCount} search bots\n\n";

// AI 봇 삽입
echo "Inserting AI bots...\n";
$aiSuccess = 0;
foreach ($aiBots as $bot) {
    $sql = "INSERT INTO bot_config (user_agent, bot_name, description, category, bot_type, is_allowed) 
            VALUES (?, ?, ?, ?, 'ai', 0)
            ON DUPLICATE KEY UPDATE 
                bot_name = VALUES(bot_name),
                description = VALUES(description),
                category = VALUES(category),
                bot_type = VALUES(bot_type)";
    
    try {
        executeQuery($sql, [
            $bot['user_agent'],
            $bot['name'],
            $bot['description'],
            $bot['category']
        ]);
        $aiSuccess++;
        echo ".";
    } catch (Exception $e) {
        echo "\nError inserting {$bot['name']}: " . $e->getMessage() . "\n";
        $errorCount++;
    }
}

echo "\nInserted {$aiSuccess} AI bots\n\n";

// Yeti 기본 허용
echo "Setting Yeti as allowed by default...\n";
executeQuery("UPDATE bot_config SET is_allowed = 1 WHERE user_agent = 'Yeti'");

// 통계
$result = getDbArray("SELECT COUNT(*) as count FROM bot_config");
$totalCount = $result[0]['count'];

echo "\n=================================\n";
echo "Bot insertion completed!\n";
echo "=================================\n";
echo "Total bots in database: {$totalCount}\n";
echo "Search bots: " . count($searchBots) . "\n";
echo "AI bots: " . count($aiBots) . "\n";
echo "Success: " . ($successCount + $aiSuccess) . "\n";
echo "Errors: {$errorCount}\n";
echo "=================================\n";

// 허용된 봇 목록 표시
$allowedBots = getDbArray("SELECT bot_name, user_agent FROM bot_config WHERE is_allowed = 1");
echo "\nAllowed bots:\n";
foreach ($allowedBots as $bot) {
    echo "  - {$bot['bot_name']} ({$bot['user_agent']})\n";
}

echo "\nDone!\n";
?>
