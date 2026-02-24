<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Récupérer toutes les notifications de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $notifications = Notification::forUser($user->id)
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $unreadCount = Notification::forUser($user->id)
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications->map(function ($notif) {
                        return [
                            'id' => $notif->id,
                            'type' => $notif->type,
                            'title' => $notif->title,
                            'body' => $notif->body,
                            'data' => $notif->data,
                            'is_read' => (bool) $notif->is_read,
                            'read_at' => $notif->read_at?->toIso8601String(),
                            'created_at' => $notif->created_at->toIso8601String(),
                        ];
                    }),
                    'unread_count' => $unreadCount,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications',
            ], 500);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $notification = Notification::forUser($user->id)
                ->where('id', $id)
                ->firstOrFail();

            $notification->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Notification marquée comme lue',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur marquage notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Notification introuvable',
            ], 404);
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            
            Notification::forUser($user->id)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Toutes les notifications ont été marquées comme lues',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur marquage toutes notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage des notifications',
            ], 500);
        }
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $notification = Notification::forUser($user->id)
                ->where('id', $id)
                ->firstOrFail();

            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification supprimée',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur suppression notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Notification introuvable',
            ], 404);
        }
    }

    /**
     * Récupérer le nombre de notifications non lues
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user();
            
            $count = Notification::forUser($user->id)
                ->unread()
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur comptage notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du comptage',
            ], 500);
        }
    }

    /**
     * Mettre à jour le token FCM de l'utilisateur
     */
    public function updateFcmToken(Request $request)
    {
        try {
            $request->validate([
                'fcm_token' => 'required|string',
            ]);

            $user = $request->user();
            $user->update([
                'fcm_token' => $request->fcm_token,
            ]);

            Log::info('FCM token mis à jour pour user: ' . $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Token FCM enregistré',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur mise à jour FCM token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du token',
            ], 500);
        }
    }

    /**
     * Envoyer une notification push de test à l'utilisateur connecté
     * (pour tester depuis Postman)
     */
    public function sendTestPush(Request $request)
    {
        try {
            $user = $request->user();

            if (empty($user->fcm_token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun token FCM enregistré. Ouvrez l\'app une fois pour enregistrer le token.',
                ], 400);
            }

            $title = $request->input('title', 'Test Mye');
            $body = $request->input('body', 'Ceci est une notification push de test.');

            $result = FcmService::sendNotification(
                $user->fcm_token,
                $title,
                $body,
                ['type' => 'test', 'screen' => 'notifications']
            );

            if (!($result['success'] ?? false)) {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'] ?? 'Échec de l\'envoi (vérifier FCM_SERVER_KEY dans .env)',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification push envoyée',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur sendTestPush: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
