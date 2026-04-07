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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID pour l'id
            $table->string('type');
            
            // notifiable_id en string pour supporter Code_enseignant
            $table->string('notifiable_id'); 
            $table->string('notifiable_type'); // la classe du modèle (Etudiant ou Enseignant)
            
            $table->text('data'); // données JSON
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
