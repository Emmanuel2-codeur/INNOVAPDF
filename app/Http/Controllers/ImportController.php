<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\AiServiceException;
use Illuminate\Http\Request;
use Smalot\PdfParser\Parser as PdfParser;

class ImportController extends Controller
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Import d'un CV existant (§7) : extraction textuelle du PDF puis
     * structuration par IA. L'utilisateur vérifie et corrige avant export
     * (§7 "Vérification et modification des données avant export").
     */
    public function importCv(Request $request)
    {
        if ($request->user()->hasReachedDocumentLimit()) {
            return response()->json([
                'message' => 'Limite de documents atteinte pour le plan gratuit.',
            ], 403);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        try {
            $text = (new PdfParser())->parseFile($request->file('file')->getRealPath())->getText();
        } catch (\Throwable $e) {
            return response()->json([
                'message' => "Ce PDF n'a pas pu être lu. Il est peut-être scanné (image) plutôt que du texte sélectionnable.",
            ], 422);
        }

        $text = trim($text);

        if ($text === '' || mb_strlen($text) < 30) {
            return response()->json([
                'message' => "Le contenu du CV n'a pas pu être extrait correctement (PDF probablement scanné/image).",
            ], 422);
        }

        try {
            $parsed = $this->ai->parseResumeText(mb_substr($text, 0, 8000));
        } catch (AiServiceException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json(['profile' => $parsed]);
    }
}
