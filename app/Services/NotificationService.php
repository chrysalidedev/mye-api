<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Créer une notification et envoyer un push
     */
    public static function create($userId, $type, $title, $body, $data = [])
    {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ]);

            $user = User::find($userId);
            if ($user && $user->fcm_token) {
                FcmService::sendNotification(
                    $user->fcm_token,
                    $title,
                    $body,
                    array_merge($data, [
                        'notification_id' => $notification->id,
                        'type' => $type,
                    ])
                );
            }

            Log::info("Notification créée: type=$type, user=$userId");
            return $notification;
        } catch (\Exception $e) {
            Log::error('Erreur création notification: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Notification de demande de connexion
     */
    public static function connectionRequest($receiverId, $senderName, $senderId)
    {
        return self::create(
            $receiverId,
            'connection_request',
            'Nouvelle demande de connexion',
            "$senderName souhaite se connecter avec vous",
            ['sender_id' => $senderId]
        );
    }

    /**
     * Notification de connexion acceptée
     */
    public static function connectionAccepted($receiverId, $accepterName, $accepterId)
    {
        return self::create(
            $receiverId,
            'connection_accepted',
            'Connexion acceptée',
            "$accepterName a accepté votre demande de connexion",
            ['accepter_id' => $accepterId]
        );
    }

    /**
     * Notification de match mutuel
     */
    public static function match($receiverId, $matchName, $matchId, $compatibilityScore)
    {
        return self::create(
            $receiverId,
            'match',
            'Nouveau match !',
            "Vous avez un match avec $matchName (Score: {$compatibilityScore}%)",
            [
                'match_id' => $matchId,
                'compatibility_score' => $compatibilityScore,
            ]
        );
    }

    /**
     * Notification de nouveau message
     */
    public static function newMessage($receiverId, $senderName, $senderId, $messagePreview)
    {
        return self::create(
            $receiverId,
            'message',
            "Message de $senderName",
            $messagePreview,
            [
                'sender_id' => $senderId,
                'conversation_id' => null,
            ]
        );
    }

    /**
     * Notification de like reçu
     */
    public static function likeReceived($receiverId, $likerName, $likerId)
    {
        return self::create(
            $receiverId,
            'like_received',
            'Quelqu\'un vous a liké !',
            "$likerName vous a liké",
            ['liker_id' => $likerId]
        );
    }

    /**
     * Notification système
     */
    public static function system($userId, $title, $body, $data = [])
    {
        return self::create(
            $userId,
            'system',
            $title,
            $body,
            $data
        );
    }
}
