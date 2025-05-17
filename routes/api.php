<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeminiSummarizerController;

Route::post('/summarize', [GeminiSummarizerController::class, 'summarize'])->name('summarize');
