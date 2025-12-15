<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Ordre de la question
            $table->text('question_text');
            $table->json('options'); // ["Option 1", "Option 2", "Option 3"]
            $table->integer('correct_answer'); // 1, 2, ou 3
            $table->integer('points')->default(1); // Points pour bonne réponse
            $table->enum('type', ['quiz', 'marketing'])->default('quiz'); // quiz ou marketing
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
