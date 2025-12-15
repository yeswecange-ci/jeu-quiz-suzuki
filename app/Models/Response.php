<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'contest_id',
        'participant_id',
        'question_id',
        'answer',
        'is_correct',
        'points_earned',
        'answered_at',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'answered_at' => 'datetime',
    ];

    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    // Enregistrer une nouvelle réponse avec calcul automatique
    public static function recordAnswer(
        int $contestId,
        int $participantId,
        int $questionId,
        int $answer
    ): self {
        $question = Question::findOrFail($questionId);

        $isCorrect = $question->isCorrect($answer);
        $pointsEarned = $isCorrect ? $question->points : 0;

        return self::updateOrCreate(
            [
                'contest_id' => $contestId,
                'participant_id' => $participantId,
                'question_id' => $questionId,
            ],
            [
                'answer' => $answer,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
                'answered_at' => now(),
            ]
        );
    }
}
