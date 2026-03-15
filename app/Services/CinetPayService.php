<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CinetPayService
{
    private string $apiKey;
    private string $siteId;
    private string $baseUrl = 'https://api-checkout.cinetpay.com/v2';

    public function __construct()
    {
        $this->apiKey = (string) (config('subscription.cinetpay.apikey') ?? env('CINETPAY_API_KEY', ''));
        $this->siteId = (string) (config('subscription.cinetpay.site_id') ?? env('CINETPAY_SITE_ID', ''));

        if (empty($this->apiKey) || empty($this->siteId)) {
            throw new \RuntimeException('CinetPay API key ou Site ID manquant. Vérifiez CINETPAY_API_KEY et CINETPAY_SITE_ID dans .env');
        }
    }

    /**
     * Initier un paiement CinetPay.
     * Retourne ['payment_url' => ..., 'transaction_id' => ...] ou lance une exception.
     */
    public function initiatePayment(
        string $transactionId,
        float  $amount,
        string $currency,
        string $description,
        string $customerName,
        string $customerEmail,
        string $returnUrl,
        string $notifyUrl
    ): array {
        $response = Http::post("{$this->baseUrl}/payment", [
            'apikey'              => $this->apiKey,
            'site_id'             => $this->siteId,
            'transaction_id'      => $transactionId,
            'amount'              => (int) $amount,
            'currency'            => $currency,
            'description'         => $description,
            'return_url'          => $returnUrl,
            'notify_url'          => $notifyUrl,
            'customer_name'       => $customerName,
            'customer_email'      => $customerEmail,
            'customer_phone_number' => '',
            'customer_address'    => 'N/A',
            'customer_city'       => 'Abidjan',
            'customer_country'    => 'CI',
            'customer_state'      => 'CI',
            'customer_zip_code'   => '00225',
            'channels'            => 'ALL',
            'lang'                => 'fr',
        ]);

        $data = $response->json();
        Log::info('CinetPay initiate', ['transaction_id' => $transactionId, 'response' => $data]);

        if (!$response->successful() || ($data['code'] ?? null) !== '201') {
            $msg = $data['message'] ?? 'Erreur CinetPay lors de l\'initialisation';
            throw new \RuntimeException($msg);
        }

        return [
            'payment_url'    => $data['data']['payment_url'],
            'transaction_id' => $transactionId,
        ];
    }

    /**
     * Vérifier le statut d'un paiement CinetPay.
     */
    public function checkPayment(string $transactionId): array
    {
        $response = Http::post("{$this->baseUrl}/payment/check", [
            'apikey'         => $this->apiKey,
            'site_id'        => $this->siteId,
            'transaction_id' => $transactionId,
        ]);

        $data = $response->json();
        Log::info('CinetPay check', ['transaction_id' => $transactionId, 'response' => $data]);

        return $data;
    }
}
