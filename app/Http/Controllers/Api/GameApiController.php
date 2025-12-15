<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Participant;
use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GameApiController extends Controller
{
    /**
     * Enregistrer une réponse depuis Twilio
     *
     * POST /api/game/submit-answer
     */
    public function submitAnswer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contest_id' => 'required|exists:contests,id',
            'whatsapp_number' => 'required|string',
            'question_id' => 'required|exists:questions,id',
            'answer' => 'required|integer|min:1|max:3',
            'conversation_sid' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $contest = Contest::findOrFail($request->contest_id);

            // Vérifier que le concours est actif
            if (!$contest->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce concours n\'est pas actif',
                    'status' => $contest->status
                ], 400);
            }

            // Vérifier que la question appartient au concours
            $question = Question::where('id', $request->question_id)
                ->where('contest_id', $request->contest_id)
                ->where('is_active', true)
                ->firstOrFail();

            // Créer ou récupérer le participant
            $participant = Participant::findOrCreateByWhatsApp(
                $request->whatsapp_number,
                ['conversation_sid' => $request->conversation_sid]
            );

            // Enregistrer la réponse
            $response = Response::recordAnswer(
                $contest->id,
                $participant->id,
                $question->id,
                $request->answer
            );

            // Récupérer la progression
            $progress = $contest->getParticipantProgress($participant->id);
            $score = $contest->getParticipantScore($participant->id);

            return response()->json([
                'success' => true,
                'message' => $response->is_correct ? 'Bonne réponse !' : 'Mauvaise réponse',
                'data' => [
                    'is_correct' => $response->is_correct,
                    'points_earned' => $response->points_earned,
                    'total_score' => $score,
                    'progress' => $progress,
                    'question' => [
                        'id' => $question->id,
                        'order' => $question->order,
                        'correct_answer' => $question->correct_answer, // Optionnel
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les informations d'un participant
     *
     * GET /api/game/participant/{whatsapp_number}
     */
    public function getParticipant($whatsappNumber)
    {
        $participant = Participant::where('whatsapp_number', $whatsappNumber)->first();

        if (!$participant) {
            return response()->json([
                'success' => false,
                'message' => 'Participant non trouvé'
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
            ]
        ]);
    }

    /**
     * Obtenir le statut du participant dans un concours
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
                'errors' => $validator->errors()
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
                ]
            ]);
        }

        $progress = $contest->getParticipantProgress($participant->id);
        $score = $contest->getParticipantScore($participant->id);

        return response()->json([
            'success' => true,
            'data' => [
                'has_started' => $participant->hasParticipatedIn($contest->id),
                'has_completed' => $participant->hasCompletedContest($contest->id),
                'progress' => $progress,
                'score' => $score,
            ]
        ]);
    }

    /**
     * Obtenir les questions d'un concours
     *
     * GET /api/game/questions/{contest_id}
     */
    public function getQuestions($contestId)
    {
        $contest = Contest::findOrFail($contestId);

        $questions = $contest->questions()
            ->where('is_active', true)
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
                'questions' => $questions
            ]
        ]);
    }

    /**
     * Obtenir le classement d'un concours
     *
     * GET /api/game/leaderboard/{contest_id}
     */
    public function getLeaderboard($contestId, Request $request)
    {
        $limit = $request->get('limit', 10);
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
            'data' => $data
        ]);
    }
}
