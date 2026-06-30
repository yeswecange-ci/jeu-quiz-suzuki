<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GameApiController extends Controller
{
    /**
     * Numéros (emojis) utilisés pour formater les options d'une question.
     */
    private const OPTION_EMOJIS = ['1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣'];

    /**
     * Prochaine question à poser à un participant (flow dynamique).
     *
     * Le flow Twilio appelle cet endpoint en boucle : il n'a plus aucune
     * question en dur. On détecte le concours actif, on trouve/crée le
     * participant, puis on renvoie la première question active non encore
     * répondue — ou `completed: true` quand il n'en reste plus.
     *
     * POST /api/game/next-question
     */
    public function nextQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'whatsapp_number' => 'required|string',
            'contest_id' => 'nullable|integer',
            'profile_name' => 'nullable|string',
            'conversation_sid' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'completed' => true,
                'data' => ['message' => 'Données invalides.'],
                'errors' => $validator->errors(),
            ], 422);
        }

        $contest = $this->resolveActiveContest($request->input('contest_id'));

        if (!$contest) {
            return response()->json([
                'success' => false,
                'completed' => true,
                'data' => [
                    'message' => "😕 Aucun concours n'est actif pour le moment.\n\nReviens bientôt, le jeu reprendra très vite ! 😊",
                ],
            ], 200);
        }

        $participant = $this->resolveParticipant($request);

        // Questions actives à l'instant T, déjà triées par `order`.
        $activeQuestions = $contest->questions()->activeAt(now())->get();

        $answeredIds = Response::where('contest_id', $contest->id)
            ->where('participant_id', $participant->id)
            ->pluck('question_id')
            ->all();

        $remaining = $activeQuestions->whereNotIn('id', $answeredIds)->values();
        $total = $activeQuestions->count();

        // Plus aucune question : le participant a terminé.
        if ($remaining->isEmpty()) {
            $score = $contest->getParticipantScore($participant->id);

            return response()->json([
                'success' => true,
                'completed' => true,
                'data' => [
                    'contest_id' => $contest->id,
                    'score' => $score,
                    'message' => "🎉 Félicitations ! Tu as terminé le jeu !\n✅ Tes réponses ont bien été enregistrées.\n\nMerci pour ta participation !\nReste connecté à notre page pour ne rien manquer ! ⚽🚗🎁",
                ],
            ], 200);
        }

        $question = $remaining->first();
        $current = $total - $remaining->count() + 1;

        return response()->json([
            'success' => true,
            'completed' => false,
            'data' => [
                'contest_id' => $contest->id,
                'question_id' => $question->id,
                'current' => $current,
                'total' => $total,
                'options_count' => is_array($question->options) ? count($question->options) : 0,
                'message' => $this->formatQuestionMessage($question, $current, $total),
            ],
        ], 200);
    }

    /**
     * Enregistrer une réponse depuis Twilio.
     *
     * POST /api/game/submit-answer
     */
    public function submitAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contest_id' => 'nullable|integer',
            'whatsapp_number' => 'required|string',
            'question_id' => 'required|integer',
            'answer' => 'required',
            'conversation_sid' => 'nullable|string',
            'profile_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'accepted' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $contest = $this->resolveActiveContest($request->input('contest_id'));

            if (!$contest || !$contest->isActive()) {
                return response()->json([
                    'success' => false,
                    'accepted' => false,
                    'message' => 'Ce concours n\'est pas actif',
                ], 400);
            }

            // La question doit appartenir au concours et être active maintenant.
            $question = $contest->questions()
                ->activeAt(now())
                ->where('id', $request->question_id)
                ->first();

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'accepted' => false,
                    'message' => 'Question introuvable ou inactive',
                ], 404);
            }

            // Sanitation + validation dynamique selon le nombre réel d'options.
            $answer = (int) trim((string) $request->input('answer'));
            $optionsCount = is_array($question->options) ? count($question->options) : 0;

            if ($answer < 1 || ($optionsCount > 0 && $answer > $optionsCount)) {
                return response()->json([
                    'success' => true,
                    'accepted' => false,
                    'message' => 'Réponse non reconnue',
                ], 200);
            }

            $participant = $this->resolveParticipant($request);

            // Réponses figées : on ne réécrit jamais une réponse déjà donnée.
            $existing = Response::where('contest_id', $contest->id)
                ->where('participant_id', $participant->id)
                ->where('question_id', $question->id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'accepted' => true,
                    'already_answered' => true,
                    'data' => [
                        'is_correct' => $existing->is_correct,
                        'points_earned' => $existing->points_earned,
                        'total_score' => $contest->getParticipantScore($participant->id),
                    ],
                ], 200);
            }

            $response = Response::recordAnswer(
                $contest->id,
                $participant->id,
                $question->id,
                $answer
            );

            return response()->json([
                'success' => true,
                'accepted' => true,
                'already_answered' => false,
                'data' => [
                    'is_correct' => $response->is_correct,
                    'points_earned' => $response->points_earned,
                    'total_score' => $contest->getParticipantScore($participant->id),
                    'progress' => $contest->getParticipantProgress($participant->id),
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('submitAnswer failed', [
                'error' => $e->getMessage(),
                'whatsapp_number' => $request->input('whatsapp_number'),
                'question_id' => $request->input('question_id'),
            ]);

            return response()->json([
                'success' => false,
                'accepted' => false,
                'message' => 'Erreur lors de l\'enregistrement',
            ], 500);
        }
    }

    /**
     * Obtenir les informations d'un participant.
     *
     * GET /api/game/participant/{whatsapp_number}
     */
    public function getParticipant($whatsappNumber)
    {
        $participant = Participant::where('whatsapp_number', $whatsappNumber)->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Participant non trouvé',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $participant->id,
                'whatsapp_number' => $participant->whatsapp_number,
                'name' => $participant->name,
                'total_score' => $participant->getTotalScore(),
                'wins_count' => $participant->getWinsCount(),
            ],
        ]);
    }

    /**
     * Obtenir le statut du participant dans un concours.
     *
     * GET /api/game/participant-status
     */
    public function getParticipantStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contest_id' => 'required|exists:contests,id',
            'whatsapp_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $contest = Contest::findOrFail($request->contest_id);
        $participant = Participant::where('whatsapp_number', $request->whatsapp_number)->first();

        if (!$participant) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_started' => false,
                    'progress' => ['total' => $contest->questions()->count(), 'answered' => 0, 'percentage' => 0],
                    'score' => 0,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'has_started' => $participant->hasParticipatedIn($contest->id),
                'has_completed' => $participant->hasCompletedContest($contest->id),
                'progress' => $contest->getParticipantProgress($participant->id),
                'score' => $contest->getParticipantScore($participant->id),
            ],
        ]);
    }

    /**
     * Obtenir les questions d'un concours.
     *
     * GET /api/game/questions/{contest_id}
     */
    public function getQuestions($contestId)
    {
        $contest = Contest::findOrFail($contestId);

        $questions = $contest->questions()
            ->activeAt(now())
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'order' => $question->order,
                    'question_text' => $question->question_text,
                    'options' => $question->options,
                    'points' => $question->points,
                    'type' => $question->type,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'contest' => [
                    'id' => $contest->id,
                    'title' => $contest->title,
                    'status' => $contest->status,
                    'is_active' => $contest->isActive(),
                ],
                'questions' => $questions,
            ],
        ]);
    }

    /**
     * Obtenir le classement d'un concours.
     *
     * GET /api/game/leaderboard/{contest_id}
     */
    public function getLeaderboard($contestId, Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        $contest = Contest::findOrFail($contestId);

        $leaderboard = $contest->getLeaderboard($limit);

        $data = $leaderboard->map(function ($entry, $index) {
            return [
                'rank' => $index + 1,
                'whatsapp_number' => $entry->participant->whatsapp_number,
                'name' => $entry->participant->name ?? 'Participant ' . $entry->participant->id,
                'total_score' => $entry->total_score,
                'questions_answered' => $entry->questions_answered,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Résoudre le concours actif. Si un id est fourni on le respecte (s'il est
     * actif), sinon on sélectionne automatiquement le concours actif le plus
     * récent — le flow n'a donc plus besoin de connaître le contest_id.
     */
    private function resolveActiveContest($contestId = null): ?Contest
    {
        if (!empty($contestId)) {
            $contest = Contest::find($contestId);
            if ($contest && $contest->isActive()) {
                return $contest;
            }
        }

        $now = now();

        return Contest::where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhereDate('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhereDate('end_date', '>=', $now);
            })
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Trouver ou créer le participant et mettre à jour son profil WhatsApp.
     */
    private function resolveParticipant(Request $request): Participant
    {
        $data = ['conversation_sid' => $request->input('conversation_sid')];

        if ($request->filled('profile_name')) {
            $data['profile_name'] = $request->input('profile_name');
        }

        $participant = Participant::findOrCreateByWhatsApp(
            $request->input('whatsapp_number'),
            $data
        );

        // Compléter le profil si on le reçoit après coup.
        if ($request->filled('profile_name') && $participant->profile_name !== $request->profile_name) {
            $participant->update(['profile_name' => $request->profile_name]);
        }

        return $participant;
    }

    /**
     * Construire le texte WhatsApp d'une question (énoncé + options numérotées).
     */
    private function formatQuestionMessage(Question $question, int $current, int $total): string
    {
        $lines = [];
        $lines[] = "📝 *Question {$current}/{$total}*";
        $lines[] = '';
        $lines[] = trim($question->question_text);

        $options = is_array($question->options) ? $question->options : [];
        if (!empty($options)) {
            $lines[] = '';
            foreach (array_values($options) as $i => $option) {
                $emoji = self::OPTION_EMOJIS[$i] ?? (($i + 1) . '.');
                $lines[] = " {$emoji} " . trim((string) $option);
            }

            $count = count($options);
            $choices = $count === 2 ? '1 ou 2' : "1 à {$count}";
            $lines[] = '';
            $lines[] = "👉 Réponds avec le numéro ({$choices}).";
        }

        return implode("\n", $lines);
    }
}
