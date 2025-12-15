<?php

namespace App\Services;

use App\Models\Contest;
use App\Models\Response;
use App\Models\Winner;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WinnerService
{
    /**
     * Obtenir toutes les semaines d'un concours
     */
    public function getContestWeeks(Contest $contest): Collection
    {
        if (!$contest->start_date || !$contest->end_date) {
            return collect();
        }

        $weeks = collect();
        $current = $contest->start_date->copy()->startOfWeek();
        $end = $contest->end_date->copy()->endOfWeek();
        $weekNumber = 1;

        while ($current->lte($end)) {
            $weekStart = $current->copy();
            $weekEnd = $current->copy()->endOfWeek();

            // S'assurer que la semaine ne dépasse pas la fin du concours
            if ($weekEnd->gt($contest->end_date)) {
                $weekEnd = $contest->end_date->copy();
            }

            $weeks->push([
                'number' => $weekNumber,
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'label' => "Semaine {$weekNumber} ({$weekStart->format('d/m')} - {$weekEnd->format('d/m')})",
            ]);

            $current->addWeek();
            $weekNumber++;
        }

        return $weeks;
    }

    /**
     * Obtenir la semaine actuelle d'un concours
     */
    public function getCurrentWeek(Contest $contest): ?array
    {
        $weeks = $this->getContestWeeks($contest);
        $now = now();

        return $weeks->first(function ($week) use ($now) {
            return $now->between($week['start_date'], $week['end_date']);
        });
    }

    /**
     * Sélectionner les gagnants pour une semaine spécifique
     */
    public function selectWinnersForWeek(Contest $contest, int $weekNumber, bool $overwrite = false): Collection
    {
        $weeks = $this->getContestWeeks($contest);
        $week = $weeks->firstWhere('number', $weekNumber);

        if (!$week) {
            return collect();
        }

        // Si des gagnants existent déjà pour cette semaine et qu'on ne veut pas écraser
        if (!$overwrite && $contest->winners()->where('week_number', $weekNumber)->exists()) {
            return $contest->winners()
                ->where('week_number', $weekNumber)
                ->with('participant')
                ->orderBy('rank')
                ->get();
        }

        // Supprimer les anciens gagnants de cette semaine si on écrase
        if ($overwrite) {
            $contest->winners()->where('week_number', $weekNumber)->delete();
        }

        // Récupérer le classement pour cette semaine
        $leaderboard = $this->getWeekLeaderboard($contest, $week['start_date'], $week['end_date'], $contest->max_winners);

        $winners = collect();
        $rank = 1;

        foreach ($leaderboard as $entry) {
            // Vérifier le score minimum si défini
            if ($contest->min_score_to_win > 0 && $entry->total_score < $contest->min_score_to_win) {
                continue;
            }

            $winner = Winner::create([
                'contest_id' => $contest->id,
                'participant_id' => $entry->participant_id,
                'rank' => $rank,
                'total_score' => $entry->total_score,
                'week_number' => $weekNumber,
                'week_start_date' => $week['start_date'],
                'week_end_date' => $week['end_date'],
                'notified' => false,
            ]);

            $winners->push($winner->load('participant'));
            $rank++;

            // Arrêter si on a atteint le nombre max de gagnants
            if ($rank > $contest->max_winners) {
                break;
            }
        }

        return $winners;
    }

    /**
     * Sélectionner les gagnants pour toutes les semaines passées
     */
    public function selectAllWeeklyWinners(Contest $contest, bool $overwrite = false): array
    {
        $weeks = $this->getContestWeeks($contest);
        $now = now();
        $results = [];

        foreach ($weeks as $week) {
            // Ne sélectionner que les semaines passées
            if ($week['end_date']->lt($now)) {
                $winners = $this->selectWinnersForWeek($contest, $week['number'], $overwrite);
                $results[$week['number']] = [
                    'week' => $week,
                    'winners' => $winners,
                ];
            }
        }

        return $results;
    }

    /**
     * Obtenir le classement pour une semaine spécifique
     */
    public function getWeekLeaderboard(Contest $contest, Carbon $startDate, Carbon $endDate, ?int $limit = null): Collection
    {
        $query = Response::where('contest_id', $contest->id)
            ->whereBetween('answered_at', [$startDate->startOfDay(), $endDate->endOfDay()])
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

    /**
     * Obtenir les gagnants d'une semaine
     */
    public function getWeekWinners(Contest $contest, int $weekNumber): Collection
    {
        return $contest->winners()
            ->where('week_number', $weekNumber)
            ->with('participant')
            ->orderBy('rank')
            ->get();
    }

    /**
     * Obtenir tous les gagnants groupés par semaine
     */
    public function getAllWinnersByWeek(Contest $contest): Collection
    {
        return $contest->winners()
            ->with('participant')
            ->orderBy('week_number')
            ->orderBy('rank')
            ->get()
            ->groupBy('week_number');
    }

    /**
     * Obtenir les statistiques des gagnants par semaine
     */
    public function getWeekWinnerStats(Contest $contest, int $weekNumber): array
    {
        $winners = $this->getWeekWinners($contest, $weekNumber);

        return [
            'total_winners' => $winners->count(),
            'notified_count' => $winners->where('notified', true)->count(),
            'pending_notification' => $winners->where('notified', false)->count(),
            'average_score' => $winners->avg('total_score'),
            'highest_score' => $winners->max('total_score'),
            'lowest_score' => $winners->min('total_score'),
        ];
    }

    /**
     * Notifier les gagnants d'une semaine
     */
    public function notifyWeekWinners(Contest $contest, int $weekNumber): int
    {
        $winners = $contest->winners()
            ->where('week_number', $weekNumber)
            ->where('notified', false)
            ->with('participant')
            ->get();

        $count = 0;

        foreach ($winners as $winner) {
            // TODO: Intégrer l'envoi WhatsApp via Twilio ici
            // $this->sendWhatsAppNotification($winner);

            $winner->markAsNotified();
            $count++;
        }

        return $count;
    }

    /**
     * Vérifier si un participant est gagnant pour une semaine
     */
    public function isWeekWinner(Contest $contest, int $participantId, int $weekNumber): bool
    {
        return $contest->winners()
            ->where('participant_id', $participantId)
            ->where('week_number', $weekNumber)
            ->exists();
    }

    /**
     * Obtenir le rang d'un participant pour une semaine
     */
    public function getParticipantWeekRank(Contest $contest, int $participantId, int $weekNumber): ?int
    {
        $winner = $contest->winners()
            ->where('participant_id', $participantId)
            ->where('week_number', $weekNumber)
            ->first();

        return $winner?->rank;
    }

    /**
     * ANCIENNE MÉTHODE - Sélection globale (pour compatibilité)
     * Maintenant utilise selectAllWeeklyWinners en interne
     */
    public function selectWinners(Contest $contest, bool $overwrite = false): Collection
    {
        // Si le concours a des dates, utiliser la sélection par semaine
        if ($contest->start_date && $contest->end_date) {
            $results = $this->selectAllWeeklyWinners($contest, $overwrite);

            // Retourner tous les gagnants de toutes les semaines
            return collect($results)->flatMap(fn($r) => $r['winners']);
        }

        // Sinon, utiliser l'ancienne méthode (sélection globale)
        return $this->selectGlobalWinners($contest, $overwrite);
    }

    /**
     * Sélection globale des gagnants (sans notion de semaine)
     */
    private function selectGlobalWinners(Contest $contest, bool $overwrite = false): Collection
    {
        if (!$overwrite && $contest->winners()->exists()) {
            return $contest->winners()->with('participant')->get();
        }

        if ($overwrite) {
            $contest->winners()->delete();
        }

        $leaderboard = $contest->getLeaderboard($contest->max_winners);
        $winners = collect();
        $rank = 1;

        foreach ($leaderboard as $entry) {
            if ($contest->min_score_to_win > 0 && $entry->total_score < $contest->min_score_to_win) {
                continue;
            }

            $winner = Winner::create([
                'contest_id' => $contest->id,
                'participant_id' => $entry->participant_id,
                'rank' => $rank,
                'total_score' => $entry->total_score,
                'notified' => false,
            ]);

            $winners->push($winner->load('participant'));
            $rank++;

            if ($rank > $contest->max_winners) {
                break;
            }
        }

        return $winners;
    }
}
