<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ToyyibPayService
{
    private ?string $secretKey = null;

    private ?string $baseUrl = null;

    private ?string $categoryCode;

    public function __construct()
    {
        $this->secretKey = config('toyyibpay.secret_key');
        $this->baseUrl = config('toyyibpay.base_url');
        $this->categoryCode = config('toyyibpay.category_code');

        if (empty($this->secretKey)) {
            throw new RuntimeException('ToyyibPay secret key is not configured.');
        }
    }

    public function createBill(array $data): array
    {
        $categoryCode = $this->categoryCode ?: $this->getOrCreateCategoryCode();

        $payload = [
            'userSecretKey' => $this->secretKey,
            'categoryCode' => $categoryCode,
            'billName' => $this->sanitizeBillName($data['billName']),
            'billDescription' => $this->sanitizeDescription($data['billDescription']),
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => (int) round($data['billAmount'] * 100),
            'billReturnUrl' => $data['billReturnUrl'],
            'billCallbackUrl' => $data['billCallbackUrl'],
            'billExternalReferenceNo' => $data['billExternalReferenceNo'],
            'billTo' => $data['billTo'] ?? '',
            'billEmail' => $data['billEmail'] ?? '',
            'billPhone' => $data['billPhone'] ?? '',
            'billPaymentChannel' => $data['billPaymentChannel'] ?? config('toyyibpay.payment_channel', '2'),
            'billExpiryDays' => $data['billExpiryDays'] ?? 3,
        ];

        $response = Http::asForm()
            ->timeout(30)
            ->post("{$this->baseUrl}/index.php/api/createBill", $payload);

        if (! $response->successful()) {
            throw new RuntimeException('ToyyibPay API error: ' . $response->body());
        }

        $result = $response->json();

        if (! isset($result[0]['BillCode'])) {
            throw new RuntimeException('Invalid response from ToyyibPay: ' . $response->body());
        }

        return [
            'billCode' => $result[0]['BillCode'],
            'paymentUrl' => "{$this->baseUrl}/{$result[0]['BillCode']}",
        ];
    }

    public function getBillTransactions(string $billCode): array
    {
        $response = Http::asForm()
            ->timeout(15)
            ->post("{$this->baseUrl}/index.php/api/getBillTransactions", [
                'billCode' => $billCode,
            ]);

        return $response->json() ?? [];
    }

    public function verifyCallbackHash(string $status, string $orderId, string $refno, string $receivedHash): bool
    {
        $expectedHash = md5($this->secretKey . $status . $orderId . $refno . 'ok');

        return hash_equals($expectedHash, $receivedHash);
    }

    private function getOrCreateCategoryCode(): string
    {
        $cachedCode = Cache::get('toyyibpay_category_code');

        if ($cachedCode) {
            return $cachedCode;
        }

        $response = Http::asForm()
            ->timeout(15)
            ->post("{$this->baseUrl}/index.php/api/createCategory", [
                'catname' => 'InapDesa Bookings',
                'catdescription' => 'Homestay booking payments from InapDesa',
                'userSecretKey' => $this->secretKey,
            ]);

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data[0]['CategoryCode'])) {
                Cache::forever('toyyibpay_category_code', $data[0]['CategoryCode']);

                return $data[0]['CategoryCode'];
            }
        }

        throw new RuntimeException(
            'Could not create ToyyibPay category. Please create one manually at ' .
            $this->baseUrl . " and set TOYYIBPAY_CATEGORY_CODE in .env.\nAPI response: " . $response->body()
        );
    }

    private function sanitizeBillName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9 _]/', '', $name);

        return substr(trim($name), 0, 30);
    }

    private function sanitizeDescription(string $description): string
    {
        $description = preg_replace('/[^a-zA-Z0-9 _]/', '', $description);

        return substr(trim($description), 0, 100);
    }
}