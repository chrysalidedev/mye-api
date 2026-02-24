<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');
            $table->enum('user1_action', ['none', 'like', 'pass'])->default('none');
            $table->enum('user2_action', ['none', 'like', 'pass'])->default('none');
            $table->boolean('is_mutual')->default(false); // true si les deux ont liké
            $table->decimal('distance', 8, 2)->nullable(); // Distance en mètres
            $table->integer('compatibility_score')->nullable(); // Score de compatibilité (0-100)
            $table->timestamp('matched_at')->nullable(); // Date du match mutuel
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index('user1_id');
            $table->index('user2_id');
            $table->index('is_mutual');
            $table->index(['user1_id', 'user2_id']);
            
            // Contrainte unique pour éviter les doublons
            $table->unique(['user1_id', 'user2_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
