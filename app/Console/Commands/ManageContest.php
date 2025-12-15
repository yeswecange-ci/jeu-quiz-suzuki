<?php

namespace App\Console\Commands;

use App\Models\Contest;
use App\Services\WinnerService;
use Illuminate\Console\Command;

class ManageContest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'contest:manage
                            {action : Action à effectuer (list, show, winners, activate, deactivate)}
                            {contest? : ID du concours (optionnel pour list)}';

    /**
     * The console command description.
     */
    protected $description = 'Gérer les concours depuis la ligne de commande';

    protected $winnerService;

    public function __construct(WinnerService $winnerService)
    {
        parent::__construct();
        $this->winnerService = $winnerService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $contestId = $this->argument('contest');

        return match($action) {
            'list' => $this->listContests(),
            'show' => $this->showContest($contestId),
            'winners' => $this->selectWinners($contestId),
            'activate' => $this->activateContest($contestId),
            'deactivate' => $this->deactivateContest($contestId),
            default => $this->error('Action invalide. Actions disponibles: list, show, winners, activate, deactivate')
        };
    }

    private function listContests()
    {
        $contests = Contest::withCount(['questions', 'participants', 'winners'])->get();

        if ($contests->isEmpty()) {
            $this->info('Aucun concours trouvé.');
            return 0;
        }

        $this->table(
            ['ID', 'Titre', 'Statut', 'Questions', 'Participants', 'Gagnants'],
            $contests->map(fn($c) => [
                $c->id,
                $c->title,
                $c->status,
                $c->questions_count,
                $c->participants_count,
                $c->winners_count . '/' . $c->max_winners
            ])
        );

        return 0;
    }

    private function showContest($contestId)
    {
        if (!$contestId) {
            $this->error('Veuillez fournir l\'ID du concours');
            return 1;
        }

        $contest = Contest::with(['questions', 'winners.participant'])->find($contestId);

        if (!$contest) {
            $this->error('Concours non trouvé');
            return 1;
        }

        $this->info("\n📋 Concours: {$contest->title}");
        $this->info("   Statut: {$contest->status}");
        $this->info("   Questions: {$contest->questions->count()}");
        $this->info("   Participants: {$contest->participants()->count()}");
        $this->info("   Gagnants: {$contest->winners->count()}/{$contest->max_winners}");

        if ($contest->winners->isNotEmpty()) {
            $this->info("\n🏆 Gagnants:");
            $this->table(
                ['Rang', 'Participant', 'Score', 'Notifié'],
                $contest->winners->map(fn($w) => [
                    $w->rank,
                    $w->participant->name ?? $w->participant->whatsapp_number,
                    $w->total_score,
                    $w->notified ? 'Oui' : 'Non'
                ])
            );
        }

        return 0;
    }

    private function selectWinners($contestId)
    {
        if (!$contestId) {
            $this->error('Veuillez fournir l\'ID du concours');
            return 1;
        }

        $contest = Contest::find($contestId);

        if (!$contest) {
            $this->error('Concours non trouvé');
            return 1;
        }

        $this->info("Sélection des gagnants pour: {$contest->title}");

        $winners = $this->winnerService->selectWinners($contest, true);

        $this->info("\n✅ {$winners->count()} gagnant(s) sélectionné(s) !");

        $this->table(
            ['Rang', 'Participant', 'Score'],
            $winners->map(fn($w) => [
                $w->rank,
                $w->participant->name ?? $w->participant->whatsapp_number,
                $w->total_score
            ])
        );

        return 0;
    }

    private function activateContest($contestId)
    {
        if (!$contestId) {
            $this->error('Veuillez fournir l\'ID du concours');
            return 1;
        }

        $contest = Contest::find($contestId);

        if (!$contest) {
            $this->error('Concours non trouvé');
            return 1;
        }

        $contest->update(['status' => 'active']);
        $this->info("✅ Concours '{$contest->title}' activé !");

        return 0;
    }

    private function deactivateContest($contestId)
    {
        if (!$contestId) {
            $this->error('Veuillez fournir l\'ID du concours');
            return 1;
        }

        $contest = Contest::find($contestId);

        if (!$contest) {
            $this->error('Concours non trouvé');
            return 1;
        }

        $contest->update(['status' => 'completed']);
        $this->info("✅ Concours '{$contest->title}' désactivé (statut: completed) !");

        return 0;
    }
}
