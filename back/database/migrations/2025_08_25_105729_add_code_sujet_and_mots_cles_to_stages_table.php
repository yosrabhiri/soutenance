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
    Schema::table('stages', function (Blueprint $table) {
       // $table->string('code_sujet')->nullable(); 
        $table->json('mots_cles')->nullable();
    });
}

public function down()
{
    Schema::table('stages', function (Blueprint $table) {
        $table->dropColumn(['code_sujet', 'mots_cles']);
    });
}
};