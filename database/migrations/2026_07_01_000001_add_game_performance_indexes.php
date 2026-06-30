<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            // Score / progression d'un participant dans un concours.
            $table->index(['contest_id', 'participant_id'], 'responses_contest_participant_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            // Récupération ordonnée des questions actives d'un concours.
            $table->index(['contest_id', 'is_active', 'order'], 'questions_contest_active_order_idx');
        });
    }

    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropIndex('responses_contest_participant_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_contest_active_order_idx');
        });
    }
};
