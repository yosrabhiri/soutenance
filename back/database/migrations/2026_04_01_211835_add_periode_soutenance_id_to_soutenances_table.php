<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soutenances', function (Blueprint $table) {
            $table->unsignedBigInteger('periode_soutenance_id')->nullable()->after('stage_id');

            $table->foreign('periode_soutenance_id')
                ->references('id')
                ->on('periodes_soutenances')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('soutenances', function (Blueprint $table) {
            $table->dropForeign(['periode_soutenance_id']);
            $table->dropColumn('periode_soutenance_id');
        });
    }
};
