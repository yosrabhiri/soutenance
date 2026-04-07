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
        Schema::create('reunion', function (Blueprint $table) {
    $table->id();
    $table->string('code_enseignant'); // pas foreignId
    $table->date('jour');
    $table->time('heure');
    $table->string('salle')->nullable();
    $table->string('titre')->nullable();
    $table->text('note')->nullable();
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
        Schema::dropIfExists('reunion');
    }
};
