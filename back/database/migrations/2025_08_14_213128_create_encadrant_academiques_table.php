<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   
    public function up()
{
    Schema::create('encadrants_academiques', function (Blueprint $table) {
        $table->id();
        $table->string('nom_complet');
        $table->string('email');
        $table->enum('role', [
            'enseignant',
            'directeur_departement',
            'directeur_stage'
        ])->default('enseignant');
        $table->timestamps();
        $table->json('roles');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('encadrant_academiques');
    }
};
