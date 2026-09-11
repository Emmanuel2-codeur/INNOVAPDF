<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DocumentController::class, 'index'])->name('dashboard');

    // Éditeur (S2)
    Route::get('/editor/new', [DocumentController::class, 'create'])->name('editor.create');
    Route::get('/editor/{document}', [DocumentController::class, 'edit'])->name('editor.edit');

    // CRUD documents (S2/S4)
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Export PDF & médias (S3)
    Route::get('/documents/{document}/export', [DocumentController::class, 'export'])->name('documents.export');
    Route::post('/media', [DocumentController::class, 'uploadMedia'])->name('media.upload');

    // Corbeille (S4)
    Route::get('/trash', [DocumentController::class, 'trash'])->name('documents.trash');
    Route::patch('/documents/{document}/restore', [DocumentController::class, 'restore'])
        ->name('documents.restore')->withTrashed();
    Route::delete('/documents/{document}/force', [DocumentController::class, 'forceDelete'])
        ->name('documents.force-delete')->withTrashed();
});

require __DIR__.'/settings.php';