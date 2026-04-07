<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soutenances', function (Blueprint $table) {
            $table->unsignedBigInteger('creneau_id')->nullable()->after('periode_soutenance_id');
            $table->unsignedBigInteger('salle_id');
            $table->foreign('salle_id')->references('id')->on('salles')->onDelete('restrict');
            $table->foreign('creneau_id')
                ->references('id')
                ->on('creneaux_soutenances')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('soutenances', function (Blueprint $table) {
            $table->dropForeign(['creneau_id']);
            $table->dropColumn('creneau_id');
        });
    }
};
