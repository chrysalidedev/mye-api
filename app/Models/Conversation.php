<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'user1_id',
        'user2_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
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
     * Relation avec les messages
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Dernier message de la conversation
     */
    public function lastMessage()
    {
        return $this->hasOne(Message::class)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * Obtenir l'autre utilisateur de la conversation
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
     * Vérifier si un utilisateur fait partie de la conversation
     */
    public function hasUser(int $userId): bool
    {
        return $this->user1_id === $userId || $this->user2_id === $userId;
    }

    /**
     * Compter les messages non lus pour un utilisateur
     */
    public function unreadCountFor(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Scope pour obtenir les conversations d'un utilisateur
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user1_id', $userId)
            ->orWhere('user2_id', $userId);
    }

    /**
     * Scope pour trier par dernier message
     */
    public function scopeOrderByLastMessage($query)
    {
        return $query->orderBy('last_message_at', 'desc');
    }
}
