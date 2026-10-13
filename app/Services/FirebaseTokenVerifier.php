<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
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

        try {
            JWT::$leeway = 60;
            $claims = (array) JWT::decode($idToken, $this->publicKeys());
        } catch (\Throwable $e) {
            Log::warning('Jeton Firebase refusé: ' . $e->getMessage());
            $this->fail();
        }

        if (($claims['aud'] ?? null) !== $projectId
            || ($claims['iss'] ?? null) !== "https://securetoken.google.com/{$projectId}"
            || empty($claims['sub'])
            || ($claims['auth_time'] ?? PHP_INT_MAX) > time() + 60) {
            $this->fail();
        }

        $firebase = json_decode(json_encode($claims['firebase'] ?? []), true);
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
     * Certificats publics de Google, mis en cache selon leur durée de validité.
     *
     * @return array<string, Key>
     */
    private function publicKeys(): array
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

        return array_map(fn ($cert) => new Key($cert, 'RS256'), $certs);
    }

    private function fail(): never
    {
        throw ValidationException::withMessages([
            'firebase_token' => ['Jeton d\'authentification invalide ou expiré.'],
        ]);
    }
}
