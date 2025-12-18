<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'whatsapp_number',
        'name',
        'profile_name',
        'conversation_sid',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function winners(): HasMany
    {
        return $this->hasMany(Winner::class);
    }

    // Obtenir ou créer un participant
    public static function findOrCreateByWhatsApp(string $whatsappNumber, array $data = []): self
    {
        return self::firstOrCreate(
            ['whatsapp_number' => $whatsappNumber],
            $data
        );
    }

    // Score total tous concours confondus
    public function getTotalScore(): int
    {
        return $this->responses()->sum('points_earned');
    }

    // Nombre de victoires
    public function getWinsCount(): int
    {
        return $this->winners()->count();
    }

    // Participe à un concours ?
    public function hasParticipatedIn(int $contestId): bool
    {
        return $this->responses()->where('contest_id', $contestId)->exists();
    }

    // A terminé un concours ?
    public function hasCompletedContest(int $contestId): bool
    {
        $contest = Contest::find($contestId);
        if (!$contest) {
            return false;
        }

        $totalQuestions = $contest->questions()->count();
        $answeredQuestions = $this->responses()
            ->where('contest_id', $contestId)
            ->count();

        return $totalQuestions === $answeredQuestions;
    }
}
