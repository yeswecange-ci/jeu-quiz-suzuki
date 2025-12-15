<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            // Ajouter les colonnes de semaine
            $table->integer('week_number')->after('contest_id')->nullable();
            $table->date('week_start_date')->after('week_number')->nullable();
            $table->date('week_end_date')->after('week_start_date')->nullable();

            // Modifier la contrainte unique pour inclure la semaine
            $table->dropUnique(['contest_id', 'participant_id']);
            $table->unique(['contest_id', 'participant_id', 'week_number'], 'contest_participant_week_unique');
        });
    }

    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->dropUnique('contest_participant_week_unique');
            $table->unique(['contest_id', 'participant_id']);

            $table->dropColumn(['week_number', 'week_start_date', 'week_end_date']);
        });
    }
};
