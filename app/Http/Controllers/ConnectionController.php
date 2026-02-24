<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class ConnectionController extends Controller
{
    /**
     * Envoyer une demande de connexion
     */
    public function sendRequest(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $senderId = auth()->id();
        $receiverId = $request->receiver_id;

        // Vérifier qu'on n'envoie pas à soi-même
        if ($senderId == $receiverId) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous connecter à vous-même',
            ], 400);
        }

        // Vérifier si une connexion existe déjà
        $existingConnection = Connection::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                  ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                  ->where('receiver_id', $senderId);
        })->first();

        if ($existingConnection) {
            return response()->json([
                'success' => false,
                'message' => 'Une demande de connexion existe déjà',
                'data' => $existingConnection,
            ], 400);
        }

        try {
            $connection = Connection::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => 'pending',
            ]);

            // Envoyer notification
            $sender = User::find($senderId);
            NotificationService::connectionRequest($receiverId, $sender->name, $senderId);

            return response()->json([
                'success' => true,
                'message' => 'Demande de connexion envoyée',
                'data' => $connection->load(['sender', 'receiver']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur envoi connexion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la demande',
            ], 500);
        }
    }

    /**
     * Accepter une demande de connexion
     */
    public function acceptRequest($connectionId)
    {
        $userId = auth()->id();
        
        $connection = Connection::where('id', $connectionId)
            ->where('receiver_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de connexion introuvable',
            ], 404);
        }

        try {
            $connection->update(['status' => 'accepted']);

            // Envoyer notification à l'expéditeur
            $accepter = User::find($userId);
            NotificationService::connectionAccepted($connection->sender_id, $accepter->name, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Connexion acceptée',
                'data' => $connection->load(['sender', 'receiver']),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur acceptation connexion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'acceptation',
            ], 500);
        }
    }

    /**
     * Rejeter une demande de connexion
     */
    public function rejectRequest($connectionId)
    {
        $userId = auth()->id();
        
        $connection = Connection::where('id', $connectionId)
            ->where('receiver_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de connexion introuvable',
            ], 404);
        }

        try {
            $connection->update(['status' => 'rejected']);

            return response()->json([
                'success' => true,
                'message' => 'Connexion rejetée',
                'data' => $connection,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur rejet connexion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet',
            ], 500);
        }
    }

    /**
     * Annuler une demande de connexion (par l'envoyeur)
     */
    public function cancelRequest($connectionId)
    {
        $userId = auth()->id();
        
        $connection = Connection::where('id', $connectionId)
            ->where('sender_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de connexion introuvable',
            ], 404);
        }

        try {
            $connection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Demande annulée',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur annulation connexion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation',
            ], 500);
        }
    }

    /**
     * Supprimer une connexion (déconnexion)
     */
    public function removeConnection($connectionId)
    {
        $userId = auth()->id();
        
        $connection = Connection::where('id', $connectionId)
            ->where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connexion introuvable',
            ], 404);
        }

        try {
            $connection->delete();

            return response()->json([
                'success' => true,
                'message' => 'Connexion supprimée',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur suppression connexion: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
            ], 500);
        }
    }

    /**
     * Obtenir toutes les connexions de l'utilisateur connecté
     */
    public function myConnections()
    {
        $userId = auth()->id();

        try {
            $connections = Connection::where('status', 'accepted')
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)
                          ->orWhere('receiver_id', $userId);
                })
                ->with(['sender', 'receiver'])
                ->get()
                ->map(function ($connection) use ($userId) {
                    // Retourner l'autre utilisateur (pas soi-même)
                    $connection->connected_user = $connection->sender_id == $userId 
                        ? $connection->receiver 
                        : $connection->sender;
                    return $connection;
                });

            return response()->json([
                'success' => true,
                'data' => $connections,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération connexions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération',
            ], 500);
        }
    }

    /**
     * Obtenir les demandes en attente (reçues)
     */
    public function pendingRequests()
    {
        $userId = auth()->id();

        try {
            $requests = Connection::where('receiver_id', $userId)
                ->where('status', 'pending')
                ->with('sender')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération demandes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération',
            ], 500);
        }
    }

    /**
     * Obtenir les demandes envoyées
     */
    public function sentRequests()
    {
        $userId = auth()->id();

        try {
            $requests = Connection::where('sender_id', $userId)
                ->where('status', 'pending')
                ->with('receiver')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $requests,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur récupération demandes envoyées: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération',
            ], 500);
        }
    }

    /**
     * Vérifier le statut de connexion avec un utilisateur
     */
    public function checkStatus($userId)
    {
        $currentUserId = auth()->id();

        if ($currentUserId == $userId) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => 'self',
                    'connection' => null,
                ],
            ]);
        }

        try {
            $connection = Connection::where(function ($query) use ($currentUserId, $userId) {
                $query->where('sender_id', $currentUserId)
                      ->where('receiver_id', $userId);
            })->orWhere(function ($query) use ($currentUserId, $userId) {
                $query->where('sender_id', $userId)
                      ->where('receiver_id', $currentUserId);
            })->first();

            if (!$connection) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'status' => 'none',
                        'connection' => null,
                    ],
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $connection->status,
                    'connection' => $connection,
                    'is_sender' => $connection->sender_id == $currentUserId,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur vérification statut: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification',
            ], 500);
        }
    }
}
