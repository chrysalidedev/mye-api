<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\CinetPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(private CinetPayService $cinetPay) {}

    /**
     * Retourner les plans (depuis la BDD) et le statut d'abonnement de l'utilisateur.
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $active = $user->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at')
            ->first();

        $plans = SubscriptionPlan::where('is_active', true)
            ->get()
            ->map(fn($p) => [
                'slug'            => $p->slug,
                'name'            => $p->name,
                'price_per_month' => (float) $p->price_per_month,
                'currency'        => $p->currency,
            ])
            ->values()
            ->toArray();

        return response()->json([
            'success'    => true,
            'subscribed' => (bool) $active,
            'expires_at' => $active?->expires_at?->toIso8601String(),
            'plan'       => $active?->plan,
            'plans'      => $plans,
        ]);
    }

    /**
     * Initier un paiement pour un plan avec un nombre de mois choisi.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'plan'   => 'required|string|exists:subscription_plans,slug',
            'months' => 'required|integer|min:1|max:12',
        ]);

        $planModel = SubscriptionPlan::where('slug', $request->plan)
            ->where('is_active', true)
            ->firstOrFail();

        $months = (int) $request->months;
        $amount = round($planModel->price_per_month * $months, 2);
        $user   = $request->user();

        $transactionId = 'MYE-' . strtoupper(Str::random(12));

        Subscription::create([
            'user_id'                 => $user->id,
            'plan'                    => $request->plan,
            'months'                  => $months,
            'status'                  => 'pending',
            'cinetpay_transaction_id' => $transactionId,
            'amount'                  => $amount,
            'currency'                => $planModel->currency,
        ]);

        return response()->json([
            'success'        => true,
            'transaction_id' => $transactionId,
            'amount'         => (int) $amount,
            'currency'       => $planModel->currency,
            'description'    => "Abonnement Mye – {$planModel->name} ($months mois)",
            'apikey'         => config('subscription.cinetpay.apikey') ?? env('CINETPAY_API_KEY'),
            'site_id'        => config('subscription.cinetpay.site_id') ?? env('CINETPAY_SITE_ID'),
            'notify_url'     => config('app.url') . '/api/subscription/webhook',
            'return_url'     => config('app.url') . '/api/subscription/return',
            'customer_name'  => $user->name ?? 'Client Mye',
            'customer_email' => $user->email ?? 'client@mye.app',
        ]);
    }

    /**
     * Webhook CinetPay (appelé automatiquement par CinetPay après paiement).
     */
    public function webhook(Request $request)
    {
        $transactionId = $request->input('cpm_trans_id');
        if (!$transactionId) {
            return response()->json(['message' => 'transaction_id manquant'], 400);
        }

        try {
            $data = $this->cinetPay->checkPayment($transactionId);
            $code = $data['data']['status'] ?? $data['code'] ?? null;

            $subscription = Subscription::where('cinetpay_transaction_id', $transactionId)->first();
            if (!$subscription) {
                Log::warning("Webhook CinetPay: subscription introuvable pour $transactionId");
                return response()->json(['message' => 'ok'], 200);
            }

            if ($code === 'ACCEPTED' || ($data['data']['payment_status'] ?? '') === 'ACCEPTED') {
                $months = $subscription->months ?? 1;
                $subscription->update([
                    'status'     => 'active',
                    'starts_at'  => now(),
                    'expires_at' => now()->addMonths($months),
                ]);
                Log::info("Abonnement activé: user={$subscription->user_id}, plan={$subscription->plan}, months=$months");
            } else {
                $subscription->update(['status' => 'cancelled']);
                Log::info("Paiement refusé: transaction=$transactionId, code=$code");
            }
        } catch (\Throwable $e) {
            Log::error('Erreur webhook CinetPay: ' . $e->getMessage());
        }

        return response()->json(['message' => 'ok'], 200);
    }

    /**
     * Page de retour après paiement (redirige vers l'app).
     */
    public function returnUrl(Request $request)
    {
        return redirect(config('app.frontend_url', config('app.url')) . '?payment=done');
    }
}
