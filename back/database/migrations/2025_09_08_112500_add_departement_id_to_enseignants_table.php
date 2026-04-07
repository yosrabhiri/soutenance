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
        Schema::table('enseignants', function (Blueprint $table) {
            $table->foreignId('departement_id')
                  ->constrained('departements')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enseignants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('departement_id');
            // ou bien :
            // $table->dropForeign(['departement_id']);
            // $table->dropColumn('departement_id');
        });
    }
};
