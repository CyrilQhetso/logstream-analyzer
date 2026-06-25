<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;

Route::get('/', [LogController::class, 'index'])->name('dashboard');
Route::post('/analyze', [LogController::class, 'upload'])->name('log.upload');
Route::post('/reset', function () { session()->forget('log_data'); return redirect()->route('dashboard'); })->name('log.reset');