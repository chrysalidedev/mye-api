<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Vérifie les jetons Firebase ID envoyés par l'app mobile après une
 * connexion Google ou Apple. Seules les informations contenues dans un
 * jeton vérifié doivent être utilisées pour identifier l'utilisateur.
 */
class FirebaseTokenVerifier
{
    private const CERTS_URL = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

    /**
     * @return array{uid: string, email: string|null, email_verified: bool, provider: string|null, provider_id: string|null}
     */
    public function verify(string $idToken, string $expectedProvider): array
    {
        $projectId = config('services.firebase.project_id');
        if (!$projectId) {
            throw new \RuntimeException('FIREBASE_PROJECT_ID non configuré');
        }

        $claims = $this->decodeAndVerifySignature($idToken);

        $now = time();
        if (($claims['aud'] ?? null) !== $projectId
            || ($claims['iss'] ?? null) !== "https://securetoken.google.com/{$projectId}"
            || empty($claims['sub'])
            || ($claims['exp'] ?? 0) < $now - 60
            || ($claims['iat'] ?? PHP_INT_MAX) > $now + 60
            || ($claims['auth_time'] ?? PHP_INT_MAX) > $now + 60) {
            $this->fail();
        }

        $firebase = $claims['firebase'] ?? [];
        $provider = $firebase['sign_in_provider'] ?? null;
        if ($provider !== $expectedProvider) {
            $this->fail();
        }

        return [
            'uid'            => $claims['sub'],
            'email'          => $claims['email'] ?? null,
            'email_verified' => (bool) ($claims['email_verified'] ?? false),
            'provider'       => $provider,
            'provider_id'    => $firebase['identities'][$provider][0] ?? null,
        ];
    }

    /**
     * Vérifie la signature RS256 du jeton avec les certificats publics de
     * Google et retourne son contenu.
     */
    private function decodeAndVerifySignature(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            $this->fail();
        }
        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header    = json_decode($this->base64UrlDecode($headerB64), true);
        $payload   = json_decode($this->base64UrlDecode($payloadB64), true);
        $signature = $this->base64UrlDecode($signatureB64);

        if (!is_array($header) || !is_array($payload) || ($header['alg'] ?? null) !== 'RS256') {
            $this->fail();
        }

        $cert = $this->publicCertificates()[$header['kid'] ?? ''] ?? null;
        if (!$cert) {
            Log::warning('Jeton Firebase refusé: certificat inconnu');
            $this->fail();
        }

        $valid = openssl_verify("{$headerB64}.{$payloadB64}", $signature, $cert, OPENSSL_ALGO_SHA256);
        if ($valid !== 1) {
            Log::warning('Jeton Firebase refusé: signature invalide');
            $this->fail();
        }

        return $payload;
    }

    private function base64UrlDecode(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4), true);
    }

    /**
     * Certificats publics de Google (kid => certificat PEM), mis en cache
     * selon leur durée de validité.
     *
     * @return array<string, string>
     */
    private function publicCertificates(): array
    {
        $certs = Cache::get('firebase_public_certs');

        if (!$certs) {
            $response = Http::timeout(10)->get(self::CERTS_URL);
            if (!$response->successful()) {
                throw new \RuntimeException('Impossible de récupérer les certificats Firebase');
            }
            $certs = $response->json();

            preg_match('/max-age=(\d+)/', (string) $response->header('Cache-Control'), $m);
            Cache::put('firebase_public_certs', $certs, (int) ($m[1] ?? 3600));
        }

        return $certs;
    }

    private function fail(): never
    {
        throw ValidationException::withMessages([
            'firebase_token' => ['Jeton d\'authentification invalide ou expiré.'],
        ]);
    }
}
