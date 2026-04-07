<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     public function up(): void
    {
        Schema::create('creneaux_soutenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periode_soutenance_id');
            $table->date('date');
            $table->string('code_slot', 10); // S1, S2, S3...
            $table->time('heure_debut');
            $table->time('heure_fin');
            $table->unsignedTinyInteger('ordre');
            $table->timestamps();

            $table->foreign('periode_soutenance_id')
                ->references('id')
                ->on('periodes_soutenances')
                ->cascadeOnDelete();

            $table->unique(['periode_soutenance_id', 'date', 'code_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creneaux_soutenances');
    }
};
