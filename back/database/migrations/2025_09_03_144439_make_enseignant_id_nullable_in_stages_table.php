<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            // Supprimer la foreign key existante
            $table->dropForeign(['enseignant_id']);
        });

        Schema::table('stages', function (Blueprint $table) {
            // Supprimer la colonne
            $table->dropColumn('enseignant_id');
        });

        Schema::table('stages', function (Blueprint $table) {
            // Recréer la colonne nullable et la clé étrangère
            $table->string('enseignant_id')->nullable();
            $table->foreign('enseignant_id')
                  ->references('Code_enseignant')
                  ->on('enseignants')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('stages', function (Blueprint $table) {
            $table->dropForeign(['enseignant_id']);
            $table->dropColumn('enseignant_id');
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->string('enseignant_id');
            $table->foreign('enseignant_id')
                  ->references('Code_enseignant')
                  ->on('enseignants')
                  ->onDelete('cascade');
        });
    }
};
