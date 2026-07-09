<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use App\Services\WinnerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    protected $winnerService;

    public function __construct(WinnerService $winnerService)
    {
        $this->winnerService = $winnerService;
    }

    /**
     * Exporter la liste de tous les participants à un concours (CSV ou PDF)
     */
    public function participants(Contest $contest, string $format)
    {
        $participants = $contest->getUniqueParticipants();

        $rows = $participants->map(function ($participant) use ($contest) {
            $progress = $contest->getParticipantProgress($participant->id);

            return [
                $participant->name ?? $participant->profile_name ?? '-',
                $participant->whatsapp_number ?? '-',
                (int) $contest->getParticipantScore($participant->id),
                $progress['answered'] . ' / ' . $progress['total'],
                $progress['percentage'] . '%',
            ];
        })
        ->sortByDesc(fn ($row) => $row[2])
        ->values()
        ->toArray();

        $headers = ['Nom', 'Numéro WhatsApp', 'Score total', 'Questions répondues', 'Complétion'];
        $filename = 'participants-' . Str::slug($contest->title);
        $title = 'Participants — ' . $contest->title;
        $subtitle = $participants->count() . ' participant(s)';

        return $this->export($format, $filename, $title, $subtitle, $headers, $rows);
    }

    /**
     * Exporter la liste de tous les gagnants (toutes les semaines) d'un concours
     */
    public function winners(Contest $contest, string $format)
    {
        $winnersByWeek = $this->winnerService->getAllWinnersByWeek($contest);

        $rows = [];
        foreach ($winnersByWeek as $weekNumber => $winners) {
            foreach ($winners as $winner) {
                $rows[] = [
                    'Semaine ' . $weekNumber,
                    $winner->rank,
                    $winner->participant->name ?? $winner->participant->profile_name ?? '-',
                    $winner->participant->whatsapp_number ?? '-',
                    $winner->total_score . ' pts',
                    $winner->notified ? 'Oui' : 'Non',
                    $winner->notified_at ? $winner->notified_at->format('d/m/Y H:i') : '-',
                ];
            }
        }

        $headers = ['Semaine', 'Rang', 'Nom', 'Numéro WhatsApp', 'Score', 'Notifié', 'Date notification'];
        $filename = 'gagnants-' . Str::slug($contest->title);
        $title = 'Gagnants par semaine — ' . $contest->title;
        $subtitle = count($rows) . ' gagnant(s) sur ' . $winnersByWeek->count() . ' semaine(s)';

        return $this->export($format, $filename, $title, $subtitle, $headers, $rows);
    }

    /**
     * Exporter les gagnants d'une semaine précise
     */
    public function weekWinners(Contest $contest, int $weekNumber, string $format)
    {
        $winners = $this->winnerService->getWeekWinners($contest, $weekNumber);

        $rows = $winners->map(function ($winner) {
            return [
                $winner->rank,
                $winner->participant->name ?? $winner->participant->profile_name ?? '-',
                $winner->participant->whatsapp_number ?? '-',
                $winner->total_score . ' pts',
                $winner->notified ? 'Oui' : 'Non',
                $winner->notified_at ? $winner->notified_at->format('d/m/Y H:i') : '-',
            ];
        })->toArray();

        $headers = ['Rang', 'Nom', 'Numéro WhatsApp', 'Score', 'Notifié', 'Date notification'];
        $filename = 'gagnants-semaine-' . $weekNumber . '-' . Str::slug($contest->title);
        $title = 'Gagnants Semaine ' . $weekNumber . ' — ' . $contest->title;
        $subtitle = $winners->count() . ' gagnant(s)';

        return $this->export($format, $filename, $title, $subtitle, $headers, $rows);
    }

    /**
     * Aiguiller vers le bon format d'export
     */
    private function export(string $format, string $filename, string $title, string $subtitle, array $headers, array $rows)
    {
        if ($format === 'pdf') {
            return Pdf::loadView('exports.pdf', compact('title', 'subtitle', 'headers', 'rows'))
                ->setPaper('a4', 'landscape')
                ->download($filename . '.pdf');
        }

        return $this->csv($filename, $headers, $rows);
    }

    /**
     * Générer un fichier CSV téléchargeable (compatible Excel FR : séparateur ; + BOM UTF-8)
     */
    private function csv(string $filename, array $headers, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');

            // BOM UTF-8 pour un affichage correct des accents dans Excel
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }

            fclose($output);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
