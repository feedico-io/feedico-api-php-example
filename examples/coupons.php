#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/env.php';
require_once __DIR__ . '/../src/FeedicoClient.php';

feedico_load_env(__DIR__ . '/../.env');

$token = feedico_env('FEEDICO_API_TOKEN');
if ($token === null) {
    fwrite(STDERR, "Set FEEDICO_API_TOKEN in .env (see .env.example).\n");
    exit(1);
}

$page     = (int) (feedico_env('FEEDICO_PAGE', '1') ?? '1');
$pageSize = (int) (feedico_env('FEEDICO_PAGE_SIZE', '10') ?? '10');
$provider = feedico_env('FEEDICO_PROVIDER');
$firmName = feedico_env('FEEDICO_FIRM_NAME');

$client = new FeedicoClient($token);

try {
    $payload = $client->listCoupons($page, $pageSize, $provider, $firmName);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$rows = FeedicoClient::extractRows($payload);
echo 'Coupons on page ' . $page . ': ' . count($rows) . PHP_EOL . PHP_EOL;

foreach ($rows as $row) {
    $title    = $row['title'] ?? $row['description'] ?? '(no title)';
    $code     = $row['couponCode'] ?? $row['code'] ?? '';
    $merchant = $row['firmName'] ?? $row['merchantName'] ?? '';
    $ends     = $row['endsAt'] ?? $row['endDate'] ?? '';

    $line = sprintf('- %s', $title);
    if ($code !== '') {
        $line .= ' | code: ' . $code;
    }
    if ($merchant !== '') {
        $line .= ' | ' . $merchant;
    }
    if ($ends !== '') {
        $line .= ' | until ' . $ends;
    }
    echo $line . PHP_EOL;
}
