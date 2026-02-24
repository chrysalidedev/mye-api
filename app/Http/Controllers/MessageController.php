<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Liste des conversations de l'utilisateur connecté
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $conversations = Conversation::forUser($user->id)
                ->with(['user1', 'user2', 'lastMessage.sender'])
                ->orderByLastMessage()
                ->get()
                ->map(function ($conversation) use ($user) {
                    $otherUser = $conversation->getOtherUser($user->id);
                    $unreadCount = $conversation->unreadCountFor($user->id);
                    $lastMessage = $conversation->lastMessage;
                    
                    // Log de débogage
                    Log::info('Conversation debug', [
                        'conversation_id' => $conversation->id,
                        'last_message_at' => $conversation->last_message_at,
                        'lastMessage_exists' => $lastMessage !== null,
                        'lastMessage_content' => $lastMessage ? $lastMessage->content : null,
                    ]);
                    
                    return [
                        'id' => $conversation->id,
                        'other_user' => $otherUser ? [
                            'id' => $otherUser->id,
                            'name' => $otherUser->name,
                            'avatar' => $otherUser->avatar,
                            'role' => $otherUser->role,
                        ] : null,
                        'last_message' => $lastMessage ? [
                            'id' => $lastMessage->id,
                            'content' => $lastMessage->content,
                            'sender_id' => $lastMessage->sender_id,
                            'is_read' => (bool) $lastMessage->is_read,
                            'created_at' => $lastMessage->created_at->toIso8601String(),
                        ] : null,
                        'unread_count' => $unreadCount,
                        'last_message_at' => $conversation->last_message_at?->toIso8601String(),
                        'created_at' => $conversation->created_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $conversations,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des conversations: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des conversations',
            ], 500);
        }
    }

    /**
     * Obtenir ou créer une conversation avec un utilisateur
     */
    public function getOrCreateConversation(Request $request, $otherUserId)
    {
        try {
            $user = $request->user();
            
            if ($user->id == $otherUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas créer une conversation avec vous-même',
                ], 400);
            }

            // Vérifier que l'autre utilisateur existe
            $otherUser = User::find($otherUserId);
            if (!$otherUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable',
                ], 404);
            }

            // Chercher une conversation existante (dans les deux sens)
            $conversation = Conversation::where(function ($query) use ($user, $otherUserId) {
                $query->where('user1_id', $user->id)
                    ->where('user2_id', $otherUserId);
            })->orWhere(function ($query) use ($user, $otherUserId) {
                $query->where('user1_id', $otherUserId)
                    ->where('user2_id', $user->id);
            })->first();

            // Créer la conversation si elle n'existe pas
            if (!$conversation) {
                $conversation = Conversation::create([
                    'user1_id' => $user->id,
                    'user2_id' => $otherUserId,
                ]);
                
                Log::info('Nouvelle conversation créée', [
                    'conversation_id' => $conversation->id,
                    'user1_id' => $user->id,
                    'user2_id' => $otherUserId,
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'avatar' => $otherUser->avatar,
                        'role' => $otherUser->role,
                    ],
                    'created_at' => $conversation->created_at->toIso8601String(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création/récupération de la conversation: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la conversation',
            ], 500);
        }
    }

    /**
     * Récupérer les messages d'une conversation
     */
    public function getMessages(Request $request, $conversationId)
    {
        try {
            $user = $request->user();
            $limit = $request->input('limit', 50);
            $before = $request->input('before'); // ID du message pour pagination

            $conversation = Conversation::find($conversationId);

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation introuvable',
                ], 404);
            }

            // Vérifier que l'utilisateur fait partie de la conversation
            if (!$conversation->hasUser($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            // Récupérer les messages
            $query = Message::where('conversation_id', $conversationId)
                ->with('sender:id,name,avatar')
                ->orderByDate('desc');

            if ($before) {
                $query->where('id', '<', $before);
            }

            $messages = $query->limit($limit)->get()->reverse()->values();

            // Marquer les messages non lus comme lus
            Message::where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'data' => $messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'conversation_id' => $message->conversation_id,
                        'sender_id' => $message->sender_id,
                        'sender' => [
                            'id' => $message->sender->id,
                            'name' => $message->sender->name,
                            'avatar' => $message->sender->avatar,
                        ],
                        'content' => $message->content,
                        'is_read' => (bool) $message->is_read,
                        'read_at' => $message->read_at?->toIso8601String(),
                        'created_at' => $message->created_at->toIso8601String(),
                    ];
                }),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des messages: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des messages',
            ], 500);
        }
    }

    /**
     * Envoyer un message
     */
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();
            $conversation = Conversation::find($request->conversation_id);

            // Vérifier que l'utilisateur fait partie de la conversation
            if (!$conversation->hasUser($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            DB::beginTransaction();

            // Créer le message
            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'sender_id' => $user->id,
                'content' => $request->content,
            ]);

            // Mettre à jour la conversation
            $conversation->update([
                'last_message_at' => now(),
            ]);

            DB::commit();

            // Charger les relations
            $message->load('sender:id,name,avatar');

            Log::info('Message envoyé', [
                'message_id' => $message->id,
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender_id' => $message->sender_id,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'avatar' => $message->sender->avatar,
                    ],
                    'content' => $message->content,
                    'is_read' => (bool) $message->is_read,
                    'read_at' => $message->read_at?->toIso8601String(),
                    'created_at' => $message->created_at->toIso8601String(),
                ],
                'message' => 'Message envoyé avec succès',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'envoi du message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du message',
            ], 500);
        }
    }

    /**
     * Marquer un message comme lu
     */
    public function markAsRead(Request $request, $messageId)
    {
        try {
            $user = $request->user();
            $message = Message::find($messageId);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message introuvable',
                ], 404);
            }

            $conversation = $message->conversation;

            // Vérifier que l'utilisateur fait partie de la conversation
            if (!$conversation->hasUser($user->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            // Ne peut pas marquer son propre message comme lu
            if ($message->sender_id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas marquer votre propre message comme lu',
                ], 400);
            }

            $message->markAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Message marqué comme lu',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage du message: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du marquage du message',
            ], 500);
        }
    }

    /**
     * Nombre total de messages non lus
     */
    public function unreadCount(Request $request)
    {
        try {
            $user = $request->user();

            $count = Message::whereHas('conversation', function ($query) use ($user) {
                $query->forUser($user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du comptage des messages non lus: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du comptage',
            ], 500);
        }
    }
}
