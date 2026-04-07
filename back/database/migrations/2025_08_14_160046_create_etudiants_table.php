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
        Schema::create('etudiants', function (Blueprint $table) {
    $table->id();
    $table->string('numero_inscription', 20);
    $table->string('cin', 20)->nullable();
    $table->date('date_delivrance_cin')->nullable();
    $table->string('lieu_delivrance_cin')->nullable();
    $table->string('prenom');
    $table->string('nom');
     $table->string('email');
    $table->string('password');
    $table->string('adresse');
    $table->string('code_postal', 10);
    $table->date('date_naissance');
    $table->string('lieu_naissance');
    $table->string('nationalite');
    $table->string('telephone', 20)->nullable();
    $table->string('cnss')->nullable();
    $table->string('profession')->nullable();
    $table->string('employeur')->nullable();
    $table->enum('etat_civil', ['célibataire', 'marié', 'divorcé', 'veuf'])->nullable();
    $table->enum('etat_militaire', ['non_concerné', 'obligatoire', 'dispensé'])->nullable();
    $table->enum('genre', ['masculin', 'féminin']);
    $table->string('photo')->nullable();
    $table->string('annee_universitaire');
    $table->year('annee_bac');
    $table->decimal('moyenne_bac', 4, 2);
    $table->enum('session_bac', ['principale', 'contrôle']);
    $table->enum('mention_bac', ['passable', 'assez bien', 'bien', 'très bien', 'excellent']);
    $table->string('section_bac');
    $table->string('pays_bac');
    $table->string('statut_universitaire');
    $table->foreignId('diplome_id')->constrained('diplomes')->onDelete('restrict');
    $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('restrict');
    $table->foreignId('specialite_id')->constrained('specialites')->onDelete('restrict');
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
        Schema::dropIfExists('etudiants');
    }
};
