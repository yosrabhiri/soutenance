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
        Schema::create('salles', function (Blueprint $table) {
             $table->id();
            $table->string('nom'); // ex: Salle A, B101, etc.
            $table->unsignedBigInteger('departement_id'); // chaque salle appartient à un département
            
            $table->timestamps();

            $table->foreign('departement_id')
                  ->references('id')
                  ->on('departements')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salles');
    }
};
