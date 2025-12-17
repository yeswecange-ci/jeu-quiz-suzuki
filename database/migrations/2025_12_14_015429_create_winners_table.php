<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->integer('rank');
            $table->integer('total_score');
            $table->integer('week_number')->nullable();
            $table->date('week_start_date')->nullable();
            $table->date('week_end_date')->nullable();
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->string('prize')->nullable();
            $table->timestamps();

            // Un participant peut gagner plusieurs fois dans différentes semaines
            $table->unique(['contest_id', 'participant_id', 'week_number'], 'contest_participant_week_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
