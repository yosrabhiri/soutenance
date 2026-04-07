<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('periode_soutenance_salles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periode_soutenance_id');
            $table->unsignedBigInteger('salle_id');
            $table->timestamps();

            $table->foreign('periode_soutenance_id')
                ->references('id')
                ->on('periodes_soutenances')
                ->cascadeOnDelete();

            $table->foreign('salle_id')
                ->references('id')
                ->on('salles')
                ->cascadeOnDelete();

            $table->unique(['periode_soutenance_id', 'salle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_soutenance_salles');
    }
};
