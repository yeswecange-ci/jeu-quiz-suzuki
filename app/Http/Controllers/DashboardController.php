<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Models\Participant;
use App\Models\Response;
use App\Models\Winner;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index()
    {
        $stats = [
            'total_contests' => Contest::count(),
            'active_contests' => Contest::where('status', 'active')->count(),
            'total_participants' => Participant::count(),
            'total_responses' => Response::count(),
            'total_winners' => Winner::count(),
        ];

        // Concours récents
        $recentContests = Contest::withCount(['questions', 'participants', 'responses'])
            ->latest()
            ->take(5)
            ->get();

        // Derniers participants
        $recentParticipants = Participant::with('responses')
            ->latest()
            ->take(10)
            ->get();

        // Graphique des participations par jour (7 derniers jours)
        $participationChart = $this->getParticipationChart();

        // Top performers
        $topParticipants = $this->getTopParticipants();

        return view('dashboard', compact(
            'stats',
            'recentContests',
            'recentParticipants',
            'participationChart',
            'topParticipants'
        ));
    }

    /**
     * Données pour le graphique de participation
     */
    private function getParticipationChart()
    {
        $data = Response::selectRaw('DATE(answered_at) as date, COUNT(DISTINCT participant_id) as count')
            ->where('answered_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray(),
            'data' => $data->pluck('count')->toArray(),
        ];
    }

    /**
     * Top 10 participants tous concours confondus
     */
    private function getTopParticipants()
    {
        return Response::selectRaw('participant_id, SUM(points_earned) as total_points, COUNT(*) as total_answers')
            ->groupBy('participant_id')
            ->orderByDesc('total_points')
            ->take(10)
            ->with('participant')
            ->get();
    }
}
