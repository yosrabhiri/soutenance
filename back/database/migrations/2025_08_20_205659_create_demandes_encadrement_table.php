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
        Schema::create('demandes_encadrement', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('etudiant_id');
    $table->string('enseignant_id'); // ici on utilise string car Code_enseignant est une chaîne
    $table->enum('statut', ['en_attente', 'acceptee', 'refusee'])->default('en_attente');
    $table->text('message')->nullable();
    $table->timestamps();

    // Clés étrangères
    $table->foreign('etudiant_id')
          ->references('id')->on('etudiants')
          ->onDelete('cascade');

    $table->foreign('enseignant_id')
          ->references('Code_enseignant')->on('enseignants')
          ->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demandes_encadrement');
    }
};
