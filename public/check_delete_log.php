<?php
// 임시 로그 확인 스크립트
header('Content-Type: text/plain; charset=utf-8');
echo "=== 최근 댓글 삭제 관련 로그 ===\n\n";
$log = shell_exec('tail -100 /var/log/nginx/mvc_error.log 2>&1 | grep -E "댓글|Route Debug|commentDelete"');
echo $log ?: "로그 없음\n";
