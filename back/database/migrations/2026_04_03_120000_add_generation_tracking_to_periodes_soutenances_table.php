<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodes_soutenances', function (Blueprint $table) {
            $table->string('generation_status')->default('idle')->after('heure_fin');
            $table->string('generation_algorithm')->nullable()->after('generation_status');
            $table->timestamp('generation_started_at')->nullable()->after('generation_algorithm');
            $table->timestamp('generation_finished_at')->nullable()->after('generation_started_at');
            $table->text('generation_error')->nullable()->after('generation_finished_at');
            $table->json('generation_meta')->nullable()->after('generation_error');
        });
    }

    public function down(): void
    {
        Schema::table('periodes_soutenances', function (Blueprint $table) {
            $table->dropColumn([
                'generation_status',
                'generation_algorithm',
                'generation_started_at',
                'generation_finished_at',
                'generation_error',
                'generation_meta',
            ]);
        });
    }
};
