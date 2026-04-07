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
        Schema::create('stages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('etudiant_id')->constrained('etudiants')->onDelete('cascade');
        $table->foreignId('societe_id')->constrained('societes')->onDelete('cascade');
        $table->foreignId('encadrant_professionnel_id')->constrained('encadrant_professionnels')->onDelete('cascade');
        $table->string('enseignant_id')->nullable();
        $table->foreign('enseignant_id')
              ->references('Code_enseignant')
              ->on('enseignants')
              ->onDelete('cascade');
        $table->text('description_taches');
        $table->date('date_debut');
        $table->date('date_fin');
        $table->string('chemin_document')->nullable(); // attestation, convention, etc.
        $table->enum('etat_validation', ['en_attente', 'accepte', 'refuse'])->default('en_attente');
        $table->string('attestation_path')->nullable(); // fichier attestation
        $table->string('url_overleaf')->nullable(); // lien rapport
        $table->enum('validation_academique', ['en_attente', 'valide', 'refuse'])->default('en_attente');
        $table->enum('type', ['ete', 'pfe', 'initiation'])->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stages');
    }


};
