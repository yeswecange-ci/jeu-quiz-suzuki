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
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
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

    // Vérifier si la question est active à une date donnée
    public function isActiveAt($date = null): bool
    {
        // Si la question est désactivée manuellement
        if (!$this->is_active) {
            return false;
        }

        $date = $date ?? now();

        // Si pas de dates définies, la question est toujours active
        if (!$this->start_date && !$this->end_date) {
            return true;
        }

        // Vérifier la date de début
        if ($this->start_date && $date->lt($this->start_date)) {
            return false;
        }

        // Vérifier la date de fin
        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    // Scope pour récupérer les questions actives à une date donnée
    public function scopeActiveAt($query, $date = null)
    {
        $date = $date ?? now();

        return $query->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->where(function ($q2) use ($date) {
                    // Cas 1: Pas de dates définies
                    $q2->whereNull('start_date')->whereNull('end_date');
                })
                ->orWhere(function ($q2) use ($date) {
                    // Cas 2: Dans la plage de dates
                    $q2->where(function ($q3) use ($date) {
                        $q3->whereNull('start_date')->orWhere('start_date', '<=', $date);
                    })
                    ->where(function ($q3) use ($date) {
                        $q3->whereNull('end_date')->orWhere('end_date', '>=', $date);
                    });
                });
            });
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
