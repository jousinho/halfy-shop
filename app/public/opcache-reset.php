<?php

declare(strict_types=1);

$tokenFile = dirname(__DIR__) . '/.opcache_token';
$token = file_exists($tokenFile) ? trim((string) file_get_contents($tokenFile)) : '';

if (!$token || ($_GET['token'] ?? '') !== $token) {
    http_response_code(403);
    exit;
}

opcache_reset();
http_response_code(200);
echo 'ok';
