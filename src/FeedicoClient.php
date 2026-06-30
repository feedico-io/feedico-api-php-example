<?php
/**
 * Minimal Feedico REST client — merchants (networks) & coupons.
 *
 * @see https://feedico.io/docs
 */

declare(strict_types=1);

final class FeedicoClient
{
    private const API_ROOT = 'https://api.feedico.io/api/v1';

    private string $token;

    public function __construct(string $token)
    {
        $this->token = trim($token);
        if ($this->token === '') {
            throw new InvalidArgumentException('FEEDICO_API_TOKEN is required.');
        }
    }

    /** @return array<string, mixed> */
    public function listMerchants(int $page = 1, int $pageSize = 50, ?string $provider = null, ?string $firmName = null): array
    {
        return $this->post(
            self::API_ROOT . '/me/networks',
            $this->listBody($page, $pageSize, $provider, $firmName)
        );
    }

    /** @return array<string, mixed> */
    public function listCoupons(int $page = 1, int $pageSize = 50, ?string $provider = null, ?string $firmName = null): array
    {
        return $this->post(
            self::API_ROOT . '/me/coupons',
            $this->listBody($page, $pageSize, $provider, $firmName)
        );
    }

    /**
     * Normalize API list payloads to a flat array of records.
     *
     * @param mixed $payload
     * @return list<array<string, mixed>>
     */
    public static function extractRows($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (self::isList($payload)) {
            return self::filterAssocRows($payload);
        }

        foreach (['networks', 'coupons', 'items'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return self::filterAssocRows($payload[$key]);
            }
        }

        return [];
    }

    /** @param array<string, mixed> $body */
    private function post(string $url, array $body): array
    {
        $json = json_encode($body, JSON_THROW_ON_ERROR);

        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('curl_init failed.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
        ]);

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('HTTP request failed: ' . ($error !== '' ? $error : 'errno ' . $errno));
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException('Invalid JSON from Feedico (HTTP ' . $status . ').');
        }

        if ($status >= 400) {
            $message = $data['error'] ?? $data['message'] ?? ('HTTP ' . $status);
            throw new RuntimeException(is_string($message) ? $message : json_encode($message));
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function listBody(int $page, int $pageSize, ?string $provider, ?string $firmName): array
    {
        return [
            'page'     => max(1, $page),
            'pageSize' => max(1, min(500, $pageSize)),
            'provider' => $provider,
            'firmName' => $firmName ?? '',
        ];
    }

    /** @param array<mixed> $value */
    private static function isList(array $value): bool
    {
        return $value === [] || array_keys($value) === range(0, count($value) - 1);
    }

    /** @param array<mixed> $rows @return list<array<string, mixed>> */
    private static function filterAssocRows(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $out[] = $row;
            }
        }
        return $out;
    }
}
