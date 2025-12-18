<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'whatsapp_number',
        'max_winners',
        'status',
        'start_date',
        'end_date',
        'min_score_to_win',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class)->orderBy('rank');
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'responses')
            ->withPivot('answer', 'is_correct', 'points_earned')
            ->withTimestamps()
            ->distinct();
    }

    // Obtenir les participants uniques (méthode correcte)
    public function getUniqueParticipants()
    {
        $participantIds = $this->responses()
            ->distinct()
            ->pluck('participant_id');

        return Participant::whereIn('id', $participantIds)->get();
    }

    // Compter les participants uniques
    public function countUniqueParticipants(): int
    {
        return $this->responses()
            ->distinct()
            ->count('participant_id');
    }

    // Calculer le score total d'un participant
    public function getParticipantScore($participantId): int
    {
        return $this->responses()
            ->where('participant_id', $participantId)
            ->sum('points_earned');
    }

    // Nombre de questions répondues par un participant
    public function getParticipantProgress($participantId): array
    {
        $totalQuestions = $this->questions()->count();
        $answeredQuestions = $this->responses()
            ->where('participant_id', $participantId)
            ->count();

        return [
            'total' => $totalQuestions,
            'answered' => $answeredQuestions,
            'percentage' => $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 2) : 0,
        ];
    }

    // Check si le concours est actif
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    // Obtenir le classement
    public function getLeaderboard(int $limit = null)
    {
        $query = $this->responses()
            ->selectRaw('participant_id, SUM(points_earned) as total_score, COUNT(*) as questions_answered')
            ->groupBy('participant_id')
            ->orderByDesc('total_score')
            ->orderByDesc('questions_answered')
            ->with('participant');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
