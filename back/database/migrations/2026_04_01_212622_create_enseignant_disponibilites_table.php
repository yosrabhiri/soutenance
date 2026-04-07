<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enseignant_disponibilites', function (Blueprint $table) {
            $table->id();
            $table->string('enseignant_id');
            $table->unsignedBigInteger('creneau_id');
            $table->string('statut', 20)->default('available');
            $table->timestamps();

            $table->foreign('enseignant_id')
                ->references('Code_enseignant')
                ->on('enseignants')
                ->cascadeOnDelete();

            $table->foreign('creneau_id')
                ->references('id')
                ->on('creneaux_soutenances')
                ->cascadeOnDelete();

            $table->unique(['enseignant_id', 'creneau_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enseignant_disponibilites');
    }
};
