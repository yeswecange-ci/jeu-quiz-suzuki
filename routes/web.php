<?php

use App\Http\Controllers\ContestController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Routes protégées par authentification
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion des concours
    Route::resource('contests', ContestController::class);

    // Actions spéciales sur les concours - GESTION PAR SEMAINE
    Route::post('/contests/{contest}/select-week-winners', [ContestController::class, 'selectWeekWinners'])
        ->name('contests.select-week-winners');
    Route::post('/contests/{contest}/select-all-week-winners', [ContestController::class, 'selectAllWeekWinners'])
        ->name('contests.select-all-week-winners');
    Route::post('/contests/{contest}/notify-week-winners', [ContestController::class, 'notifyWeekWinners'])
        ->name('contests.notify-week-winners');
    Route::get('/contests/{contest}/week/{weekNumber}', [ContestController::class, 'showWeekLeaderboard'])
        ->name('contests.week-leaderboard');

    // Exports CSV / PDF
    Route::get('/contests/{contest}/export/participants/{format}', [ExportController::class, 'participants'])
        ->where('format', 'csv|pdf')
        ->name('contests.export.participants');
    Route::get('/contests/{contest}/export/winners/{format}', [ExportController::class, 'winners'])
        ->where('format', 'csv|pdf')
        ->name('contests.export.winners');
    Route::get('/contests/{contest}/week/{weekNumber}/export/{format}', [ExportController::class, 'weekWinners'])
        ->where('format', 'csv|pdf')
        ->name('contests.export.week-winners');

    // Anciennes routes (pour compatibilité)
    Route::post('/contests/{contest}/select-winners', [ContestController::class, 'selectWinners'])
        ->name('contests.select-winners');
    Route::post('/contests/{contest}/notify-winners', [ContestController::class, 'notifyWinners'])
        ->name('contests.notify-winners');

    // Gestion des participants
    Route::resource('participants', ParticipantController::class)->except(['create', 'store']);

    // Gestion des questions (nested resource)
    Route::resource('contests.questions', QuestionController::class);

    // Réorganiser les questions
    Route::post('/contests/{contest}/questions/reorder', [QuestionController::class, 'reorder'])
        ->name('contests.questions.reorder');

    // Profil utilisateur (Laravel Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
