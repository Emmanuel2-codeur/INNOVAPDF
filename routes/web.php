<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Connexion Google (Socialite) — accessible sans être connecté, évidemment.
Route::get('/auth/google/redirect', [App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])->name('auth.google.callback');

// Éditeur en libre accès (parcours invité) : on peut créer et prévisualiser un
// document sans compte. La sauvegarde/export réel exige un compte (routes
// protégées plus bas) ; l'export "brouillon" ci-dessous permet quand même de
// télécharger un PDF avant de s'inscrire, comme demandé pour l'UX d'accueil.
Route::get('/editor/new', [DocumentController::class, 'create'])->name('editor.create');
Route::middleware('throttle:10,1')->post('/guest/export', [App\Http\Controllers\PublicController::class, 'exportDraft'])->name('guest.export');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DocumentController::class, 'index'])->name('dashboard');

    // Édition d'un document existant : nécessite un compte (vérification de
    // propriété via policy) — seule la création est ouverte aux invités.
    Route::get('/editor/{document}', [DocumentController::class, 'edit'])->name('editor.edit');

    // Import de CV existant (S6/§7) — limité, coûteux en IA + parsing PDF.
    Route::middleware('throttle:5,1')->post('/import-cv', [App\Http\Controllers\ImportController::class, 'importCv'])->name('import.cv');

    // CRUD documents (S2/S4)
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Export PDF (limité : la génération PDF est coûteuse en CPU) & médias
    // (limité : évite l'épuisement du stockage par upload en boucle). §22/§23.
    Route::middleware('throttle:15,1')->group(function () {
        Route::get('/documents/{document}/export', [DocumentController::class, 'export'])->name('documents.export');
        Route::post('/media', [DocumentController::class, 'uploadMedia'])->name('media.upload');
    });

    // Corbeille (S4)
    Route::get('/trash', [DocumentController::class, 'trash'])->name('documents.trash');
    Route::patch('/documents/{document}/restore', [DocumentController::class, 'restore'])
        ->name('documents.restore')->withTrashed();
    Route::delete('/documents/{document}/force', [DocumentController::class, 'forceDelete'])
        ->name('documents.force-delete')->withTrashed();

    // Partage par lien secret (S7)
    Route::post('/documents/{document}/share', [DocumentController::class, 'share'])->name('documents.share');
    Route::delete('/documents/{document}/share', [DocumentController::class, 'unshare'])->name('documents.unshare');

    // Assistant IA (S5) — limité à 20 appels/minute par utilisateur pour éviter
    // le gaspillage d'appels API (cahier des charges §23 "Limitation des appels IA inutiles").
    Route::middleware('throttle:20,1')->prefix('ai')->group(function () {
        Route::post('/transform', [App\Http\Controllers\AiController::class, 'transform'])->name('ai.transform');
        Route::post('/translate', [App\Http\Controllers\AiController::class, 'translate'])->name('ai.translate');
        Route::post('/summary', [App\Http\Controllers\AiController::class, 'summary'])->name('ai.summary');
        Route::post('/ats', [App\Http\Controllers\AiController::class, 'ats'])->name('ai.ats');
    });
});

// Consultation publique via lien secret (S7) — volontairement HORS du groupe
// 'auth' : n'importe qui possédant l'URL exacte peut consulter/exporter, sans compte.
// Limité en fréquence pour empêcher un brute-force des jetons UUID (peu probable
// vu leur entropie, mais défense en profondeur — §22).
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/share/{token}', [App\Http\Controllers\PublicController::class, 'show'])->name('public.show');
    Route::get('/share/{token}/pdf', [App\Http\Controllers\PublicController::class, 'exportPdf'])->name('public.export');
});

require __DIR__.'/settings.php';
