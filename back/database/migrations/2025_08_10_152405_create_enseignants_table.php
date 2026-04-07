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
        Schema::create('enseignants', function (Blueprint $table) {
             $table->string('Code_enseignant')->primary();
            $table->string('password');
            $table->string('Année_recrutement')->nullable();
            $table->string('NomEnseignant')->nullable();
            $table->string('PrenomEnseignant')->nullable();
            $table->string('Nom_Prenom_Enseignant')->nullable();
            $table->string('Code_EnsCh')->nullable();
            $table->string('Code_Grade')->nullable();
            $table->string('Coef_Kilometrique')->nullable();
            $table->string('Type_Impot')->nullable();
            $table->string('Code_Discpline')->nullable();
            $table->string('Code_Departement')->nullable();
            $table->string('Code_Perm')->nullable();
            $table->string('Sirveillance')->nullable();
            $table->string('Cide_Stat')->nullable();
            $table->string('Nom_Prenom_Ar')->nullable();
            $table->string('Orre_paiement')->nullable();
            $table->string('Code_Diplome')->nullable();
            $table->string('Email')->unique();
            $table->string('Sexe')->nullable();
            $table->string('CIN')->nullable();
            $table->string('Nouveau_Ancien')->nullable();
            $table->string('RIB')->nullable();
            $table->enum('role', [
            'enseignant',
            'directeur_departement',
            'directeur_stage'
            ])->default('enseignant');
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
        Schema::dropIfExists('enseignants');
    }
};
