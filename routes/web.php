<?php

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPdf\Facades\Pdf;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::get('/test-pdf', function () {
    return Pdf::html('<h1>Test INNOVAPDF</h1><p>' . now() . '</p>')
        ->format('a4')
        ->driver('dompdf')
        ->download('test.pdf');
});

Route::get('/test-ai', function (\App\Services\AiService $ai) {
    return $ai->correct('Elle a manger une pomme hier soire.');

    dd($result);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Routes pour l'éditeur du Sprint S2
    Route::get('/editor/new', [App\Http\Controllers\DocumentController::class, 'create'])->name('editor.create');
    Route::get('/editor/{document}', [App\Http\Controllers\DocumentController::class, 'edit'])->name('editor.edit');
    Route::post('/documents', [App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
});