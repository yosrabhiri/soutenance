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
        Schema::create('etudiant_reunion', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
     $table->foreignId('etudiant_id')->constrained()->onDelete('cascade');
    $table->foreignId('reunion_id')->constrained('reunion')->onDelete('cascade'); // bien pointer sur l'id
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etudiant_reunion');
    }
};
