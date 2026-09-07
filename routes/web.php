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

Route::get('/test-ai', function () {
    try {
        // On essaie de charger le service manuellement
        $ai = app(\App\Services\AiService::class);
        
        $result = $ai->correct('Elle a manger une pomme hier soire.');
        dd($result);

    } catch (\Exception $e) {
        // Si c'est une erreur classique (ex: API injoignable)
        dd("Erreur Exception : " . $e->getMessage());
    } catch (\Error $e) {
        // Si c'est une erreur fatale (ex: fichier introuvable, classe mal nommée)
        dd("Erreur Fatale : " . $e->getMessage());
    }
});