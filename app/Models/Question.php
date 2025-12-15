<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'order',
        'question_text',
        'options',
        'correct_answer',
        'points',
        'type',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    // Vérifier si une réponse est correcte
    public function isCorrect(int $answer): bool
    {
        return $answer === $this->correct_answer;
    }

    // Statistiques de la question
    public function getStats(): array
    {
        $total = $this->responses()->count();
        $correct = $this->responses()->where('is_correct', true)->count();

        return [
            'total_responses' => $total,
            'correct_responses' => $correct,
            'incorrect_responses' => $total - $correct,
            'success_rate' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
        ];
    }

    // Distribution des réponses
    public function getResponseDistribution(): array
    {
        $distribution = $this->responses()
            ->selectRaw('answer, COUNT(*) as count')
            ->groupBy('answer')
            ->pluck('count', 'answer')
            ->toArray();

        return [
            1 => $distribution[1] ?? 0,
            2 => $distribution[2] ?? 0,
            3 => $distribution[3] ?? 0,
        ];
    }
}
