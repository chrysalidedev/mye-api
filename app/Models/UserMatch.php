<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMatch extends Model
{
    protected $table = 'matches';
    
    protected $fillable = [
        'user1_id',
        'user2_id',
        'user1_action',
        'user2_action',
        'is_mutual',
        'distance',
        'compatibility_score',
        'matched_at',
    ];

    protected $casts = [
        'is_mutual' => 'boolean',
        'distance' => 'decimal:2',
        'compatibility_score' => 'integer',
        'matched_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relation avec le premier utilisateur
     */
    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    /**
     * Relation avec le deuxième utilisateur
     */
    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    /**
     * Obtenir l'autre utilisateur du match
     */
    public function getOtherUser(int $currentUserId): ?User
    {
        if ($this->user1_id === $currentUserId) {
            return $this->user2;
        }
        
        if ($this->user2_id === $currentUserId) {
            return $this->user1;
        }
        
        return null;
    }

    /**
     * Vérifier si c'est un match mutuel
     */
    public function isMutual(): bool
    {
        return $this->user1_action === 'like' && $this->user2_action === 'like';
    }

    /**
     * Obtenir l'action d'un utilisateur
     */
    public function getUserAction(int $userId): string
    {
        if ($this->user1_id === $userId) {
            return $this->user1_action;
        }
        
        if ($this->user2_id === $userId) {
            return $this->user2_action;
        }
        
        return 'none';
    }

    /**
     * Scope pour obtenir les matchs d'un utilisateur
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user1_id', $userId)
            ->orWhere('user2_id', $userId);
    }

    /**
     * Scope pour obtenir les matchs mutuels
     */
    public function scopeMutual($query)
    {
        return $query->where('is_mutual', true);
    }

    /**
     * Scope pour obtenir les likes envoyés
     */
    public function scopeLikesSent($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user1_id', $userId)->where('user1_action', 'like')
              ->orWhere('user2_id', $userId)->where('user2_action', 'like');
        });
    }

    /**
     * Scope pour obtenir les likes reçus
     */
    public function scopeLikesReceived($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user1_id', $userId)->where('user2_action', 'like')->where('user1_action', 'none')
              ->orWhere('user2_id', $userId)->where('user1_action', 'like')->where('user2_action', 'none');
        });
    }
}
