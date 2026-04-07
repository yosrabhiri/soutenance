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
        Schema::create('encadrant_professionnels', function (Blueprint $table) {
            
        $table->id();
        $table->foreignId('societe_id')->constrained('societes')->onDelete('cascade');
        $table->string('nom_complet');
        $table->string('fonction');
        $table->string('departement');
        $table->string('email');
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
        Schema::dropIfExists('encadrant_professionnels');
    }
};
