<?php

use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

Route::get('/test-pdf', function () {
    $pdf = Pdf::loadHTML('<h1>Test INNOVAPDF</h1><p>' . now() . '</p>');
    return $pdf->download('test.pdf');
});

Route::get('/test-ai', function (\App\Services\AiService $ai) {
    return $ai->correct('Elle a manger une pomme hier soire.');
});