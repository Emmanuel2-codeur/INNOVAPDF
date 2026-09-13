<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class PublicController extends Controller
{
    /**
     * Consultation publique d'un document via son lien secret non listé.
     * Cahier des charges §10-11 : accessible sans compte, pas de moteur de
     * recherche interne, uniquement à qui possède l'URL exacte.
     */
    public function show(string $token)
    {
        $document = Document::where('share_token', $token)->firstOrFail();

        return Inertia::render('PublicView', ['document' => $document, 'token' => $token]);
    }

    public function exportPdf(string $token)
    {
        $document = Document::where('share_token', $token)->firstOrFail();

        $view = match ("{$document->type}.{$document->template}") {
            'cv.minimal' => 'pdf.cv-minimal',
            'cv.modern' => 'pdf.cv-modern',
            'cover_letter.default' => 'pdf.cover-letter',
            'invoice.classic' => 'pdf.invoice-classic',
            'quote.simple' => 'pdf.quote-simple',
            'attestation.default' => 'pdf.certificate',
            'certificate.default' => 'pdf.certificate',
            default => null,
        };

        abort_if($view === null, 422);

        $filename = str($document->title ?: 'document')->slug()->append('.pdf')->toString();

        return Pdf::view($view, ['doc' => $document])->format('a4')->driver('dompdf')->download($filename);
    }
}
