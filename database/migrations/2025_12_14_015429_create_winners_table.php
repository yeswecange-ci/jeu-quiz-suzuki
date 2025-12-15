<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->integer('rank'); // 1er, 2ème, etc.
            $table->integer('total_score');
            $table->boolean('notified')->default(false);
            $table->timestamp('notified_at')->nullable();
            $table->text('prize')->nullable();
            $table->timestamps();

            $table->unique(['contest_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winners');
    }
};
