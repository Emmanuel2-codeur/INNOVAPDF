<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\AiServiceException;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(private readonly AiService $ai) {}

    /**
     * Correction / reformulation / raccourcir / développer un champ de texte.
     * Cahier des charges §6 : "Insertion directe du résultat après validation"
     * — on renvoie juste le résultat, l'insertion se fait côté front après
     * que l'utilisateur ait cliqué "Appliquer".
     */
    public function transform(Request $request)
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'action' => ['required', 'string', 'in:correct,reformulate,shorten,expand'],
        ]);

        try {
            return response()->json(['result' => $this->ai->transform($data['text'], $data['action'])]);
        } catch (AiServiceException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function translate(Request $request)
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'target_language' => ['required', 'string', 'max:50'],
        ]);

        try {
            return response()->json(['result' => $this->ai->translate($data['text'], $data['target_language'])]);
        } catch (AiServiceException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    public function summary(Request $request)
    {
        $data = $request->validate([
            'context' => ['required', 'array'],
        ]);

        try {
            return response()->json(['result' => $this->ai->generateSummary($data['context'])]);
        } catch (AiServiceException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }

    /**
     * Analyse ATS : compare le CV à une offre d'emploi (§6 cahier des charges).
     */
    public function ats(Request $request)
    {
        $data = $request->validate([
            'cv_text' => ['required', 'string', 'max:10000'],
            'job_offer' => ['required', 'string', 'max:10000'],
        ]);

        try {
            return response()->json(['result' => $this->ai->analyzeAts($data['cv_text'], $data['job_offer'])]);
        } catch (AiServiceException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }
    }
}
