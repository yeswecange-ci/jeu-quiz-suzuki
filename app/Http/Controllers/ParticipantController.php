<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Contest;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    /**
     * Liste des participants
     */
    public function index(Request $request)
    {
        $query = Participant::query()->withCount('responses');

        // Filtrer par concours si spécifié
        if ($request->has('contest_id')) {
            $contestId = $request->contest_id;
            $query->whereHas('responses', function ($q) use ($contestId) {
                $q->where('contest_id', $contestId);
            });
        }

        // Recherche par numéro ou nom
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('whatsapp_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('profile_name', 'like', "%{$search}%");
            });
        }

        $participants = $query->latest()->paginate(15);

        // Récupérer tous les concours pour le filtre
        $contests = Contest::select('id', 'title')->get();

        return view('participants.index', compact('participants', 'contests'));
    }

    /**
     * Afficher un participant avec toutes ses réponses
     */
    public function show(Participant $participant)
    {
        $participant->load(['responses.contest', 'responses.question', 'winners.contest']);

        // Grouper les réponses par concours
        $responsesByContest = $participant->responses->groupBy('contest_id')->map(function ($responses, $contestId) use ($participant) {
            $contest = Contest::find($contestId);
            $totalScore = $responses->sum('points_earned');
            $correctAnswers = $responses->where('is_correct', true)->count();

            return [
                'contest' => $contest,
                'responses' => $responses->sortBy('question.order'),
                'total_score' => $totalScore,
                'correct_answers' => $correctAnswers,
                'total_questions' => $responses->count(),
                'completion_rate' => $contest ? ($responses->count() / $contest->questions()->count() * 100) : 0,
            ];
        });

        $stats = [
            'total_contests' => $responsesByContest->count(),
            'total_responses' => $participant->responses->count(),
            'total_score' => $participant->getTotalScore(),
            'total_wins' => $participant->getWinsCount(),
        ];

        return view('participants.show', compact('participant', 'responsesByContest', 'stats'));
    }

    /**
     * Formulaire d'édition d'un participant
     */
    public function edit(Participant $participant)
    {
        return view('participants.edit', compact('participant'));
    }

    /**
     * Mettre à jour un participant
     */
    public function update(Request $request, Participant $participant)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'profile_name' => 'nullable|string|max:255',
            'whatsapp_number' => 'required|string|unique:participants,whatsapp_number,' . $participant->id,
        ]);

        $participant->update($validated);

        return redirect()->route('participants.show', $participant)
            ->with('success', 'Participant mis à jour avec succès !');
    }

    /**
     * Supprimer un participant
     */
    public function destroy(Participant $participant)
    {
        $participant->delete();

        return redirect()->route('participants.index')
            ->with('success', 'Participant supprimé avec succès !');
    }
}
