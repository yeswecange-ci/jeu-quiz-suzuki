<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Services\WinnerService;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    protected $winnerService;

    public function __construct(WinnerService $winnerService)
    {
        $this->winnerService = $winnerService;
    }

    /**
     * Liste des concours
     */
    public function index()
    {
        $contests = Contest::withCount(['questions', 'winners'])
            ->latest()
            ->paginate(10);

        // Ajouter le count correct des participants pour chaque concours
        $contests->each(function ($contest) {
            $contest->unique_participants_count = $contest->countUniqueParticipants();
        });

        return view('contests.index', compact('contests'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('contests.create');
    }

    /**
     * Enregistrer un nouveau concours
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'whatsapp_number' => 'nullable|string',
            'max_winners' => 'required|integer|min:1',
            'min_score_to_win' => 'required|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:draft,active,completed,archived',
        ]);

        $contest = Contest::create($validated);

        return redirect()->route('contests.show', $contest)
            ->with('success', 'Concours créé avec succès !');
    }

    /**
     * Afficher un concours avec gestion des semaines
     */
    public function show(Contest $contest)
    {
        $contest->load(['questions', 'winners.participant']);

        $stats = [
            'total_participants' => $contest->countUniqueParticipants(),
            'total_responses' => $contest->responses()->count(),
            'completion_rate' => $this->calculateCompletionRate($contest),
            'average_score' => $this->calculateAverageScore($contest),
        ];

        // Obtenir toutes les semaines du concours
        $weeks = $this->winnerService->getContestWeeks($contest);
        $currentWeek = $this->winnerService->getCurrentWeek($contest);

        // Gagnants groupés par semaine
        $winnersByWeek = $this->winnerService->getAllWinnersByWeek($contest);

        // Classement général (toutes semaines confondues)
        $leaderboard = $contest->getLeaderboard(20);

        return view('contests.show', compact('contest', 'stats', 'weeks', 'currentWeek', 'winnersByWeek', 'leaderboard'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Contest $contest)
    {
        return view('contests.edit', compact('contest'));
    }

    /**
     * Mettre à jour un concours
     */
    public function update(Request $request, Contest $contest)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'whatsapp_number' => 'nullable|string',
            'max_winners' => 'required|integer|min:1',
            'min_score_to_win' => 'required|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:draft,active,completed,archived',
        ]);

        $contest->update($validated);

        return redirect()->route('contests.show', $contest)
            ->with('success', 'Concours mis à jour avec succès !');
    }

    /**
     * Supprimer un concours
     */
    public function destroy(Contest $contest)
    {
        $contest->delete();

        return redirect()->route('contests.index')
            ->with('success', 'Concours supprimé avec succès !');
    }

    /**
     * Sélectionner les gagnants pour une semaine spécifique
     */
    public function selectWeekWinners(Request $request, Contest $contest)
    {
        $weekNumber = $request->input('week_number');

        if (!$weekNumber) {
            return redirect()->route('contests.show', $contest)
                ->with('error', 'Numéro de semaine manquant.');
        }

        $winners = $this->winnerService->selectWinnersForWeek($contest, $weekNumber, true);

        return redirect()->route('contests.show', $contest)
            ->with('success', "Semaine {$weekNumber}: " . count($winners) . ' gagnant(s) sélectionné(s) !');
    }

    /**
     * Sélectionner les gagnants pour toutes les semaines passées
     */
    public function selectAllWeekWinners(Contest $contest)
    {
        $results = $this->winnerService->selectAllWeeklyWinners($contest, true);

        $totalWinners = collect($results)->sum(fn($r) => count($r['winners']));

        return redirect()->route('contests.show', $contest)
            ->with('success', "{$totalWinners} gagnant(s) sélectionné(s) pour " . count($results) . " semaine(s) !");
    }

    /**
     * Notifier les gagnants d'une semaine
     */
    public function notifyWeekWinners(Request $request, Contest $contest)
    {
        $weekNumber = $request->input('week_number');

        if (!$weekNumber) {
            return redirect()->route('contests.show', $contest)
                ->with('error', 'Numéro de semaine manquant.');
        }

        $count = $this->winnerService->notifyWeekWinners($contest, $weekNumber);

        return redirect()->route('contests.show', $contest)
            ->with('success', "Semaine {$weekNumber}: {$count} gagnant(s) notifié(s) !");
    }

    /**
     * ANCIENNE MÉTHODE - Pour compatibilité (redirige vers toutes les semaines)
     */
    public function selectWinners(Contest $contest)
    {
        return $this->selectAllWeekWinners($contest);
    }

    /**
     * ANCIENNE MÉTHODE - Pour compatibilité
     */
    public function notifyWinners(Contest $contest)
    {
        // Notifier tous les gagnants non notifiés
        $winners = $contest->winners()->where('notified', false)->get();
        $count = 0;

        foreach ($winners as $winner) {
            $winner->markAsNotified();
            $count++;
        }

        return redirect()->route('contests.show', $contest)
            ->with('success', $count . ' gagnant(s) notifié(s) !');
    }

    /**
     * Afficher le classement d'une semaine
     */
    public function showWeekLeaderboard(Contest $contest, $weekNumber)
    {
        $weeks = $this->winnerService->getContestWeeks($contest);
        $week = $weeks->firstWhere('number', $weekNumber);

        if (!$week) {
            abort(404, 'Semaine non trouvée');
        }

        $leaderboard = $this->winnerService->getWeekLeaderboard(
            $contest,
            $week['start_date'],
            $week['end_date']
        );

        $winners = $this->winnerService->getWeekWinners($contest, $weekNumber);

        return view('contests.week-leaderboard', compact('contest', 'week', 'leaderboard', 'winners'));
    }

    /**
     * Calculer le taux de complétion
     */
    private function calculateCompletionRate(Contest $contest): float
    {
        $totalQuestions = $contest->questions()->count();
        if ($totalQuestions === 0) {
            return 0;
        }

        $participants = $contest->getUniqueParticipants();
        if ($participants->isEmpty()) {
            return 0;
        }

        $completedCount = $participants->filter(function ($participant) use ($contest) {
            return $participant->hasCompletedContest($contest->id);
        })->count();

        return round(($completedCount / $participants->count()) * 100, 2);
    }

    /**
     * Calculer le score moyen par participant (pas par réponse)
     */
    private function calculateAverageScore(Contest $contest): float
    {
        $participants = $contest->getUniqueParticipants();

        if ($participants->isEmpty()) {
            return 0;
        }

        $totalScore = $participants->sum(function ($participant) use ($contest) {
            return $contest->getParticipantScore($participant->id);
        });

        return round($totalScore / $participants->count(), 2);
    }
}
