<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\NotificationService;

class MatchController extends Controller
{
    /**
     * Mettre à jour la position de l'utilisateur
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
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
            
            $user->update([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_updated_at' => now(),
            ]);

            Log::info('Position mise à jour', [
                'user_id' => $user->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Position mise à jour avec succès',
                'data' => [
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'location_updated_at' => $user->location_updated_at->toIso8601String(),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la position: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la position',
            ], 500);
        }
    }

    /**
     * Obtenir les utilisateurs à proximité (rayon de 500m)
     */
    public function getNearbyUsers(Request $request)
    {
        try {
            $user = $request->user();
            
            // Vérifier que l'utilisateur a une position
            if (!$user->latitude || !$user->longitude) {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez activer la géolocalisation',
                ], 400);
            }

            $radius = 0.5; // 500m = 0.5km
            
            // Formule Haversine pour calculer la distance
            $nearbyUsers = User::select('users.*')
                ->selectRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$user->latitude, $user->longitude, $user->latitude]
                )
                ->where('id', '!=', $user->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having('distance', '<=', $radius)
                ->orderBy('distance', 'asc')
                ->get()
                ->map(function ($nearbyUser) use ($user) {
                    // Calculer le score de compatibilité
                    $compatibilityScore = $this->calculateCompatibilityScore($user, $nearbyUser);
                    
                    // Vérifier s'il y a déjà une interaction
                    $existingMatch = $this->getExistingMatch($user->id, $nearbyUser->id);
                    
                    return [
                        'id' => $nearbyUser->id,
                        'name' => $nearbyUser->name,
                        'avatar' => $nearbyUser->avatar,
                        'role' => $nearbyUser->role,
                        'profession' => $nearbyUser->profession,
                        'bio' => $nearbyUser->bio,
                        'city' => $nearbyUser->city,
                        'distance' => round($nearbyUser->distance * 1000, 0), // Convertir en mètres
                        'compatibility_score' => $compatibilityScore,
                        'my_action' => $existingMatch ? $existingMatch['my_action'] : 'none',
                        'their_action' => $existingMatch ? $existingMatch['their_action'] : 'none',
                        'is_mutual' => $existingMatch ? $existingMatch['is_mutual'] : false,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $nearbyUsers,
                'count' => $nearbyUsers->count(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs à proximité: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs',
            ], 500);
        }
    }

    /**
     * Liker un utilisateur
     */
    public function likeUser(Request $request, $userId)
    {
        try {
            $currentUser = $request->user();
            $targetUser = User::find($userId);

            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable',
                ], 404);
            }

            if ($currentUser->id == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas vous liker vous-même',
                ], 400);
            }

            // Calculer la distance
            $distance = $this->calculateDistance(
                $currentUser->latitude,
                $currentUser->longitude,
                $targetUser->latitude,
                $targetUser->longitude
            );

            // Calculer le score de compatibilité
            $compatibilityScore = $this->calculateCompatibilityScore($currentUser, $targetUser);

            // Vérifier s'il existe déjà un match
            $match = UserMatch::where(function ($query) use ($currentUser, $userId) {
                $query->where('user1_id', $currentUser->id)->where('user2_id', $userId);
            })->orWhere(function ($query) use ($currentUser, $userId) {
                $query->where('user1_id', $userId)->where('user2_id', $currentUser->id);
            })->first();

            if ($match) {
                // Mettre à jour l'action
                if ($match->user1_id == $currentUser->id) {
                    $match->user1_action = 'like';
                } else {
                    $match->user2_action = 'like';
                }

                // Vérifier si c'est un match mutuel
                if ($match->user1_action === 'like' && $match->user2_action === 'like') {
                    $match->is_mutual = true;
                    $match->matched_at = now();
                    
                    // Envoyer notifications de match mutuel
                    NotificationService::match($userId, $currentUser->name, $match->id, $compatibilityScore);
                    NotificationService::match($currentUser->id, $targetUser->name, $match->id, $compatibilityScore);
                } else {
                    // Envoyer notification de like reçu
                    NotificationService::likeReceived($userId, $currentUser->name, $currentUser->id);
                }

                $match->save();
            } else {
                // Créer un nouveau match
                $match = UserMatch::create([
                    'user1_id' => $currentUser->id,
                    'user2_id' => $userId,
                    'user1_action' => 'like',
                    'user2_action' => 'none',
                    'distance' => $distance,
                    'compatibility_score' => $compatibilityScore,
                ]);
                
                // Envoyer notification de like reçu
                NotificationService::likeReceived($userId, $currentUser->name, $currentUser->id);
            }

            Log::info('Like envoyé', [
                'from_user' => $currentUser->id,
                'to_user' => $userId,
                'is_mutual' => $match->is_mutual,
            ]);

            return response()->json([
                'success' => true,
                'message' => $match->is_mutual ? 'C\'est un match ! 🎉' : 'Like envoyé',
                'data' => [
                    'is_mutual' => $match->is_mutual,
                    'matched_at' => $match->matched_at?->toIso8601String(),
                    'compatibility_score' => $compatibilityScore,
                    'distance' => round($distance, 0),
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du like: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du like',
            ], 500);
        }
    }

    /**
     * Passer un utilisateur
     */
    public function passUser(Request $request, $userId)
    {
        try {
            $currentUser = $request->user();

            if ($currentUser->id == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action invalide',
                ], 400);
            }

            // Vérifier s'il existe déjà un match
            $match = UserMatch::where(function ($query) use ($currentUser, $userId) {
                $query->where('user1_id', $currentUser->id)->where('user2_id', $userId);
            })->orWhere(function ($query) use ($currentUser, $userId) {
                $query->where('user1_id', $userId)->where('user2_id', $currentUser->id);
            })->first();

            if ($match) {
                // Mettre à jour l'action
                if ($match->user1_id == $currentUser->id) {
                    $match->user1_action = 'pass';
                } else {
                    $match->user2_action = 'pass';
                }
                $match->save();
            } else {
                // Créer un nouveau match avec pass
                UserMatch::create([
                    'user1_id' => $currentUser->id,
                    'user2_id' => $userId,
                    'user1_action' => 'pass',
                    'user2_action' => 'none',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur passé',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors du pass: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du pass',
            ], 500);
        }
    }

    /**
     * Obtenir les matchs mutuels
     */
    public function getMatches(Request $request)
    {
        try {
            $user = $request->user();

            $matches = UserMatch::where('is_mutual', true)
                ->where(function ($query) use ($user) {
                    $query->where('user1_id', $user->id)
                          ->orWhere('user2_id', $user->id);
                })
                ->with(['user1', 'user2'])
                ->orderBy('matched_at', 'desc')
                ->get()
                ->map(function ($match) use ($user) {
                    $otherUser = $match->getOtherUser($user->id);
                    
                    return [
                        'match_id' => $match->id,
                        'user' => [
                            'id' => $otherUser->id,
                            'name' => $otherUser->name,
                            'avatar' => $otherUser->avatar,
                            'role' => $otherUser->role,
                            'profession' => $otherUser->profession,
                            'bio' => $otherUser->bio,
                        ],
                        'distance' => $match->distance,
                        'compatibility_score' => $match->compatibility_score,
                        'matched_at' => $match->matched_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $matches,
                'count' => $matches->count(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des matchs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des matchs',
            ], 500);
        }
    }

    /**
     * Calculer la distance entre deux points (formule Haversine)
     * Retourne la distance en mètres
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) {
            return null;
        }

        $earthRadius = 6371000; // Rayon de la Terre en mètres

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Calculer le score de compatibilité entre deux utilisateurs
     * Retourne un score entre 0 et 100
     */
    private function calculateCompatibilityScore(User $user1, User $user2)
    {
        $score = 0;

        // Même profession : +30 points
        if ($user1->profession && $user2->profession && $user1->profession === $user2->profession) {
            $score += 30;
        }

        // Même ville : +20 points
        if ($user1->city && $user2->city && $user1->city === $user2->city) {
            $score += 20;
        }

        // Compétences communes : +25 points
        if ($user1->skills && $user2->skills) {
            $commonSkills = array_intersect($user1->skills, $user2->skills);
            $score += min(count($commonSkills) * 5, 25);
        }

        // Disponibilité compatible : +15 points
        if ($user1->availability && $user2->availability) {
            $score += 15;
        }

        // Bonus de proximité : +10 points si < 100m
        if ($user1->latitude && $user2->latitude) {
            $distance = $this->calculateDistance(
                $user1->latitude,
                $user1->longitude,
                $user2->latitude,
                $user2->longitude
            );
            
            if ($distance < 100) {
                $score += 10;
            }
        }

        return min($score, 100);
    }

    /**
     * Obtenir le match existant entre deux utilisateurs
     */
    private function getExistingMatch($userId1, $userId2)
    {
        $match = UserMatch::where(function ($query) use ($userId1, $userId2) {
            $query->where('user1_id', $userId1)->where('user2_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('user1_id', $userId2)->where('user2_id', $userId1);
        })->first();

        if (!$match) {
            return null;
        }

        $myAction = $match->user1_id == $userId1 ? $match->user1_action : $match->user2_action;
        $theirAction = $match->user1_id == $userId1 ? $match->user2_action : $match->user1_action;

        return [
            'my_action' => $myAction,
            'their_action' => $theirAction,
            'is_mutual' => $match->is_mutual,
        ];
    }
}
