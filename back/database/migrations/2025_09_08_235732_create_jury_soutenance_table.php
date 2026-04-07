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
        Schema::create('jury_soutenance', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('soutenance_id');
    $table->string('enseignant_id');
    $table->string('role')->nullable(); // président, rapporteur, examinateur...
    $table->timestamps();

    $table->foreign('soutenance_id')->references('id')->on('soutenances')->onDelete('cascade');
    $table->foreign('enseignant_id')->references('Code_enseignant')->on('enseignants')->onDelete('cascade');
});

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jury_soutenance');
    }
};
