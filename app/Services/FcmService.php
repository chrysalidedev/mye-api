<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Envoyer une notification push via FCM HTTP v1 API (compte de service).
     * @return array{success: bool, error: string|null}
     */
    public static function sendNotification($fcmToken, $title, $body, $data = [])
    {
        if (!$fcmToken) {
            Log::warning('FCM token manquant');
            return ['success' => false, 'error' => 'Token FCM manquant'];
        }

        $credentialsPath = config('services.fcm.credentials') ?: env('FCM_CREDENTIALS_PATH') ?: storage_path('app/firebase-credentials.json');
        if (!is_file($credentialsPath)) {
            Log::warning("FCM v1: fichier credentials introuvable: $credentialsPath");
            return [
                'success' => false,
                'error' => 'Fichier compte de service introuvable. Placer firebase-credentials.json dans storage/app/ ou définir FCM_CREDENTIALS_PATH dans .env.',
            ];
        }

        $projectId = config('services.fcm.project_id') ?: env('FCM_PROJECT_ID');
        if (!$projectId) {
            $cred = @json_decode((string) file_get_contents($credentialsPath), true);
            $projectId = $cred['project_id'] ?? null;
        }
        if (!$projectId) {
            return [
                'success' => false,
                'error' => 'FCM_PROJECT_ID manquant. Définir dans .env ou utiliser un fichier JSON de compte de service contenant "project_id".',
            ];
        }

        try {
            $accessToken = self::getAccessToken($credentialsPath);
            if (!$accessToken) {
                return ['success' => false, 'error' => 'Impossible d\'obtenir le token d\'accès (vérifier le fichier compte de service).'];
            }

            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => array_map(fn ($v) => (string) $v, $data),
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'sound' => 'default',
                            'default_vibrate_timings' => true,
                            'channel_id' => 'high_importance_channel',
                        ],
                    ],
                    'apns' => [
                        'headers' => ['apns-priority' => '10'],
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info('Notification FCM v1 envoyée avec succès');
                return ['success' => true, 'error' => null];
            }

            $status = $response->status();
            $body = $response->body();
            Log::error("Erreur FCM v1 HTTP $status: $body");
            $error = strlen(trim($body)) > 0 ? "FCM v1 HTTP $status: " . substr(trim($body), 0, 500) : "FCM v1 HTTP $status";
            return ['success' => false, 'error' => $error];
        } catch (\Throwable $e) {
            Log::error('Exception FCM v1: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtenir un token OAuth2 à partir du fichier compte de service (JWT).
     */
    private static function getAccessToken(string $credentialsPath): ?string
    {
        $json = @file_get_contents($credentialsPath);
        if ($json === false) {
            Log::warning("FCM: impossible de lire le fichier: $credentialsPath");
            return null;
        }

        $cred = json_decode($json, true);
        if (!$cred || empty($cred['client_email']) || empty($cred['private_key'])) {
            Log::warning('FCM: fichier compte de service invalide (client_email ou private_key manquant)');
            return null;
        }

        $now = time();
        $jwtPayload = [
            'iss' => $cred['client_email'],
            'sub' => $cred['client_email'],
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ];

        $jwt = self::encodeJwt($cred['private_key'], $cred['client_email'], $jwtPayload);
        if (!$jwt) {
            return null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            Log::error('FCM OAuth2: ' . $response->body());
            return null;
        }

        $data = $response->json();
        return $data['access_token'] ?? null;
    }

    private static function encodeJwt(string $privateKeyPem, string $clientEmail, array $payload): ?string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            self::base64UrlEncode(json_encode($header)),
            self::base64UrlEncode(json_encode($payload)),
        ];

        $signatureInput = implode('.', $segments);
        $key = openssl_pkey_get_private($privateKeyPem);
        if ($key === false) {
            Log::warning('FCM: openssl_pkey_get_private a échoué');
            return null;
        }

        $signature = '';
        if (!openssl_sign($signatureInput, $signature, $key, OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $segments[] = self::base64UrlEncode($signature);
        return implode('.', $segments);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Envoyer une notification à plusieurs tokens (FCM v1, une requête par token pour simplifier).
     */
    public static function sendToMultiple(array $fcmTokens, $title, $body, $data = [])
    {
        if (empty($fcmTokens)) {
            Log::warning('Aucun FCM token fourni');
            return false;
        }

        $ok = true;
        foreach ($fcmTokens as $token) {
            $result = self::sendNotification($token, $title, $body, $data);
            if (!($result['success'] ?? false)) {
                $ok = false;
            }
        }
        return $ok;
    }
}
