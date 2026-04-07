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
    public function up(): void
    {
        Schema::table('specialites', function (Blueprint $table) {
            $table->foreignId('departement_id')
                  ->constrained('departements')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('specialites', function (Blueprint $table) {
            $table->dropConstrainedForeignId('departement_id');
        });
    }
};
