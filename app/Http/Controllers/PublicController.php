<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class PublicController extends Controller
{
    /** Mapping type+template → vue Blade, partagé par tous les points d'export PDF. */
    public static function resolvePdfView(string $type, string $template): ?string
    {
        return match ("{$type}.{$template}") {
            'cv.monochrome' => 'pdf.cv-monochrome',
            'cv.photo-banner' => 'pdf.cv-photo-banner',
            'cv.dark-sidebar' => 'pdf.cv-dark-sidebar',
            'cv.circular-photo' => 'pdf.cv-circular-photo',
            'cover_letter.rounded-green' => 'pdf.cover-letter-rounded-green',
            'cover_letter.organic-orange' => 'pdf.cover-letter-organic-orange',
            'invoice.purple', 'quote.purple' => 'pdf.invoice-purple',
            'invoice.orange-black', 'quote.orange-black' => 'pdf.invoice-orange-black',
            'invoice.red-wave', 'quote.red-wave' => 'pdf.invoice-red-wave',
            'invoice.blue-clean', 'quote.blue-clean' => 'pdf.invoice-blue-clean',
            'attestation.gold-black', 'certificate.gold-black' => 'pdf.certificate-gold-black',
            'attestation.blue-gold', 'certificate.blue-gold' => 'pdf.certificate-blue-gold',
            default => null,
        };
    }

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

        $view = self::resolvePdfView($document->type, $document->template);
        abort_if($view === null, 422);

        $filename = str($document->title ?: 'document')->slug()->append('.pdf')->toString();

        return Pdf::view($view, ['doc' => $document])->format('a4')->driver('dompdf')->download($filename);
    }

    /**
     * Export PDF d'un brouillon NON sauvegardé, pour le parcours invité :
     * la personne peut télécharger son document avant même de créer un compte.
     * Rien n'est persisté en base ici.
     */
    public function exportDraft(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:cv,cover_letter,invoice,quote,certificate,attestation',
            'template' => 'required|string|max:100',
            'content' => 'required|array',
            'style' => 'required|array',
        ]);

        $view = self::resolvePdfView($validated['type'], $validated['template']);
        abort_if($view === null, 422, "Aucun gabarit d'export PDF pour {$validated['type']}/{$validated['template']}.");

        // Instance Eloquent non persistée : ->content/->style bénéficient quand
        // même des casts du modèle (array), exactement comme un document réel.
        $doc = new Document($validated);

        $filename = str($validated['title'] ?: 'document')->slug()->append('.pdf')->toString();

        return Pdf::view($view, ['doc' => $doc])->format('a4')->driver('dompdf')->download($filename);
    }
}

