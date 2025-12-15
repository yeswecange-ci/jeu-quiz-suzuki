<?php

use App\Http\Controllers\Api\GameApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('game')->group(function () {
    // Soumettre une réponse depuis Twilio
    Route::post('/submit-answer', [GameApiController::class, 'submitAnswer']);

    // Obtenir les informations d'un participant
    Route::get('/participant/{whatsapp_number}', [GameApiController::class, 'getParticipant']);

    // Obtenir le statut du participant dans un concours
    Route::get('/participant-status', [GameApiController::class, 'getParticipantStatus']);

    // Obtenir les questions d'un concours
    Route::get('/questions/{contest_id}', [GameApiController::class, 'getQuestions']);

    // Obtenir le classement
    Route::get('/leaderboard/{contest_id}', [GameApiController::class, 'getLeaderboard']);
});

// Route de test
Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'message' => 'Quiz Game API is running',
        'timestamp' => now(),
    ]);
});
