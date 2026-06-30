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
    $payload = $client->listMerchants($page, $pageSize, $provider, $firmName);
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

$rows = FeedicoClient::extractRows($payload);
echo 'Merchants on page ' . $page . ': ' . count($rows) . PHP_EOL . PHP_EOL;

foreach ($rows as $row) {
    $name     = $row['firmName'] ?? $row['name'] ?? $row['title'] ?? '(no name)';
    $provider = $row['provider'] ?? $row['network'] ?? '';
    $id       = $row['id'] ?? $row['merchantId'] ?? '';

    echo sprintf("- [%s] %s (%s)\n", $id, $name, $provider);
}
