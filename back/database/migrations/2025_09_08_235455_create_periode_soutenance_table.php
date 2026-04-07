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
        Schema::create('periodes_soutenances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diplome_id'); // Licence, Ingénieur, Master
            $table->date('date_debut');
            $table->date('date_fin');
            $table->integer('duree_minutes')->default(60); // durée d’une soutenance (par défaut 1h30)
            $table->time('heure_debut')->default('09:00'); // heure début chaque jour
            $table->time('heure_fin')->default('17:00');   // heure fin chaque jour
            $table->timestamps();

            $table->foreign('diplome_id')->references('id')->on('diplomes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('periodes_soutenances');
    }
};

