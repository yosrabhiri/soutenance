<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::table('stages', function (Blueprint $table) { 
        $table->string('version_pdf')->nullable(); 
        $table->string('traite_par')->nullable(); 
    });
}

public function down()
{
    Schema::table('stages', function (Blueprint $table) {
        $table->dropColumn(['type', 'version_pdf', 'traite_par']);
    });
}
};