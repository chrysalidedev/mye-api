<?php

// creation de service de paiement pour hum paiement
namespace App\Services;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubPaiementServices
{
    private string $apiKey = "shk_Fsfj03bhkwZxHPhH3pYYTWwCoIvGKZN4tFxy";
    private string $marchadKey = "77abyp6fbe";
    private string $testUrl = "http://rest-airtime.paysecurehub.com/api/payhub-ws/build-away";
    private string $prodUrl = '"https://rest-airtime.paysecurehub.com/api/payhub-ws/build-away"'; // Remplacez par votre URL de callback

    public function __construct()
    {
        $this->apiKey = (string) (config('subscription.hum_paiement.apikey') ?? env('HUB_PAIEMENT_API_KEY', ''));
        if (empty($this->apiKey)) {
            throw new \RuntimeException('HumPaiement API key manquant. Vérifiez HUB_PAIEMENT_API_KEY dans .env');
        }
    }

    public function initiatePayment($data)
    {
          $reponse = Http::withHeaders(['MerchantId' => $this->marchadKey, 'ApiKey' => $this->apiKey])
                ->post($this->testUrl, $data);

                $ResJSON = $reponse->json();

                if ($reponse->status() === 200) {
                if ($ResJSON['code'] === 200) {
                    // Redirection sur le hub de paiement
                    if (!empty($ResJSON['url'])) {

                        return redirect()->away($ResJSON['url']);
                    } else {
                        $mess = "Echec d'authentification pour acceder à la page demandée !";
                        // toas($mess,'success');
                        $code = $ResJSON['code'];
                        Log::critical("Echec d'authentification pour acceder à la page demandée ! Code: {$code}");
                    }
                } else {
                    $mess = $ResJSON['message'];

                    $code = $ResJSON['code'];

                    Log::critical(" Echec d'initiation du paiement. Code: {$code}, Message: {$mess}");
                }
            }
    }

}
