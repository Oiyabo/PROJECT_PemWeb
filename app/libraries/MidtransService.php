<?php

class MidtransService
{
    private string $serverKey;
    private string $clientKey;
    private bool $isProduction;

    public function __construct()
    {
        // echo MIDTRANS_IS_PRODUCTION ? "Mode Production" : "Mode Sandbox";
        $this->serverKey    = MIDTRANS_SERVER_KEY;
        $this->clientKey    = MIDTRANS_CLIENT_KEY;
        $this->isProduction = MIDTRANS_IS_PRODUCTION;
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function getSnapScriptUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function createSnapToken(array $params): array
    {
        $url = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $response = $this->request('POST', $url, $params);

        if (empty($response['token'])) {
            $message = $response['error_messages'][0]
                ?? $response['status_message']
                ?? 'Gagal membuat token Snap Midtrans';
            throw new RuntimeException($message);
        }

        return [
            'token'    => $response['token'],
            'redirect' => $response['redirect_url'] ?? null,
        ];
    }

    public function verifyNotificationSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signatureKey
    ): bool {
        $expected = hash(
            'sha512',
            $orderId . $statusCode . $grossAmount . $this->serverKey
        );
        return hash_equals($expected, $signatureKey);
    }

    public function isSettlementStatus(string $transactionStatus): bool
    {
        return in_array($transactionStatus, ['capture', 'settlement'], true);
    }

    public function mapTransactionToLocalStatus(string $transactionStatus): string
    {
        if ($this->isSettlementStatus($transactionStatus)) {
            return 'settlement';
        }
        if (in_array($transactionStatus, ['expire', 'expired'], true)) {
            return 'expire';
        }
        if (in_array($transactionStatus, ['cancel', 'deny', 'failure'], true)) {
            return 'deny';
        }
        return 'pending';
    }

    public function buildSnapPayload(
        string $orderId,
        int $grossAmount,
        array $customer,
        string $itemName
    ): array {
        return [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => [
                [
                    'id'       => $orderId,
                    'price'    => $grossAmount,
                    'quantity' => 1,
                    'name'     => mb_substr($itemName, 0, 50),
                ],
            ],
            'customer_details' => [
                'first_name' => $customer['nama'] ?? 'Pelanggan',
                'email'      => $customer['email'] ?? 'pelanggan@bengkel.local',
            ],
            'callbacks' => [
                'finish' => BASEURL . '/midtrans/finish',
            ],
        ];
    }

    public function getTransactionStatus(string $orderId): array
    {
        $base = $this->isProduction
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
        $url = $base . '/v2/' . rawurlencode($orderId) . '/status';
        return $this->request('GET', $url, []);
    }

    private function request(string $method, string $url, array $body = []): array
    {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($this->serverKey . ':'),
            ],
            CURLOPT_TIMEOUT => 30,
        ];
        if ($method !== 'GET' && $body !== []) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($body);
        }
        curl_setopt_array($ch, $opts);

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new RuntimeException('Koneksi Midtrans gagal: ' . $error);
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Respons Midtrans tidak valid');
        }

        return $decoded;
    }
}
