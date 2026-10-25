<?php

use App\Http\Controllers\AssistantChatController;
use App\Http\Controllers\CourtHallController;
use App\Http\Controllers\CourtSimulatorController;
use App\Http\Controllers\LegalNewsController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'simulator')->name('home');
Route::view('/simulyator', 'court-hall')->name('court-hall');
Route::redirect('/bot.php', '/simulyator', 301);

Route::post('/telegram/webhook', TelegramWebhookController::class)->name('telegram.webhook');

Route::prefix('api')->name('api.')->middleware('throttle:ai')->group(function () {
    Route::post('/assistant/chat', AssistantChatController::class)->name('assistant.chat');
    Route::post('/court/turn', [CourtSimulatorController::class, 'turn'])->name('court.turn');
    Route::post('/court/cases', [CourtSimulatorController::class, 'storeCase'])->name('court.cases.store');
    Route::post('/court/verdict', [CourtSimulatorController::class, 'verdict'])->name('court.verdict');
    Route::post('/court/hint', [CourtSimulatorController::class, 'hint'])->name('court.hint');
    Route::post('/news/refresh', LegalNewsController::class)->name('news.refresh');

    Route::post('/court-hall/turn', [CourtHallController::class, 'turn'])->name('court-hall.turn');
    Route::post('/court-hall/cases', [CourtHallController::class, 'storeCase'])->name('court-hall.cases.store');
    Route::post('/court-hall/hint', [CourtHallController::class, 'hint'])->name('court-hall.hint');
    Route::post('/court-hall/verdict', [CourtHallController::class, 'verdict'])->name('court-hall.verdict');
    Route::post('/advisor', [CourtHallController::class, 'advise'])->name('advisor');
});
