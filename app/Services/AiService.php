<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /** Prompts système pour chaque action de l'assistant IA (§6 du cahier des charges). */
    private const ACTIONS = [
        'correct' => "Tu corriges l'orthographe et la grammaire du texte fourni, sans changer le sens ni le ton. Réponds uniquement avec le texte corrigé, sans commentaire.",
        'reformulate' => "Tu reformules ce texte dans un style professionnel et clair, en gardant le même sens et la même longueur approximative. Réponds uniquement avec le texte reformulé.",
        'shorten' => "Tu raccourcis ce texte de moitié environ, en gardant les informations essentielles. Réponds uniquement avec le texte raccourci.",
        'expand' => "Tu développes ce texte en ajoutant des détails professionnels pertinents et réalistes, sans inventer de faits vérifiables (dates, chiffres). Réponds uniquement avec le texte développé.",
    ];

    /**
     * Action générique de rédaction (correction, reformulation, raccourcir, développer).
     * Groq : rapide, adapté aux textes courts d'un champ de formulaire.
     *
     * @throws AiServiceException
     */
    public function transform(string $text, string $action): string
    {
        if (! isset(self::ACTIONS[$action])) {
            throw new AiServiceException("Action IA inconnue : {$action}.");
        }

        return $this->callGroq(self::ACTIONS[$action], $text);
    }

    /**
     * Import de CV (§7) : extrait un JSON structuré à partir du texte brut
     * d'un CV existant (PDF), pour préremplir l'éditeur.
     *
     * @throws AiServiceException
     */
    public function parseResumeText(string $rawText): array
    {
        $schema = '{"fullName":"","title":"","email":"","phone":"","location":"","summary":"","experiences":[{"company":"","position":"","startDate":"","endDate":"","description":""}]}';

        $json = $this->callGroq(
            "Tu extrais les informations d'un CV en JSON strict, selon exactement ce schéma : {$schema}. "
            . "Ne jamais inventer d'information absente du texte fourni (laisser vide plutôt que deviner). "
            . "Réponds UNIQUEMENT avec le JSON, sans texte autour, sans balises markdown.",
            $rawText
        );

        // Au cas où le modèle encapsulerait quand même la réponse dans des ```json ... ```.
        $json = trim(preg_replace('/^```json\s*|```$/m', '', $json));

        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new AiServiceException("Impossible d'extraire les informations du CV importé.");
        }

        return $data;
    }

    public function correct(string $text): string
    {
        return $this->transform($text, 'correct');
    }

    /**
     * Traduction vers une langue cible (ex: 'anglais', 'espagnol').
     *
     * @throws AiServiceException
     */
    public function translate(string $text, string $targetLanguage): string
    {
        return $this->callGroq(
            "Tu traduis fidèlement ce texte en {$targetLanguage}, en gardant un registre professionnel. Réponds uniquement avec la traduction.",
            $text
        );
    }

    /**
     * Génère un résumé professionnel (profil) à partir des expériences/compétences saisies.
     *
     * @throws AiServiceException
     */
    public function generateSummary(array $context): string
    {
        $prompt = "Rédige un résumé professionnel percutant (3-4 lignes maximum) pour un CV, à partir de ces informations :\n\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return $this->callGroq(
            "Tu rédiges des résumés de profil professionnels pour des CV, concis et percutants, sans inventer d'expérience non mentionnée. Réponds uniquement avec le résumé.",
            $prompt
        );
    }

    private function callGroq(string $systemPrompt, string $userContent): string
    {
        if (! config('services.groq.key')) {
            throw new AiServiceException('Clé API Groq manquante (GROQ_API_KEY).');
        }

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(15)
                ->retry(2, 250, throw: false)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'openai/gpt-oss-20b',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userContent],
                    ],
                ]);

            $response->throw();
        } catch (ConnectionException $e) {
            Log::error('AiService - connexion Groq impossible', ['error' => $e->getMessage()]);
            throw new AiServiceException('Impossible de joindre le service IA (Groq).', previous: $e);
        } catch (RequestException $e) {
            Log::error('AiService - erreur API Groq', ['status' => $e->response->status(), 'body' => $e->response->body()]);
            throw new AiServiceException('Le service IA a renvoyé une erreur.', previous: $e);
        }

        $content = $response->json('choices.0.message.content');

        if ($content === null) {
            Log::warning('AiService - réponse Groq sans contenu exploitable', ['body' => $response->json()]);
            throw new AiServiceException('Réponse du service IA invalide.');
        }

        return trim($content);
    }

    /**
     * Gemini : grand contexte, pour comparer un CV entier à une offre (analyse ATS, §6).
     *
     * @throws AiServiceException
     */
    public function analyzeAts(string $cvText, string $jobOffer): string
    {
        if (! config('services.gemini.key')) {
            throw new AiServiceException('Clé API Gemini manquante (GEMINI_API_KEY).');
        }

        $prompt = "Compare ce CV à cette offre d'emploi. Liste les mots-clés présents et manquants, puis donne un score de compatibilité sur 100. Ne pas inventer d'expérience absente du CV.\n\nCV:\n{$cvText}\n\nOffre:\n{$jobOffer}";

        try {
            $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->timeout(30)
                ->retry(2, 250, throw: false)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent',
                    ['contents' => [['parts' => [['text' => $prompt]]]]]
                );

            $response->throw();
        } catch (ConnectionException $e) {
            Log::error('AiService::analyzeAts - connexion Gemini impossible', ['error' => $e->getMessage()]);
            throw new AiServiceException('Impossible de joindre le service d\'analyse (Gemini).', previous: $e);
        } catch (RequestException $e) {
            Log::error('AiService::analyzeAts - erreur API Gemini', ['status' => $e->response->status(), 'body' => $e->response->body()]);
            throw new AiServiceException('Le service d\'analyse a renvoyé une erreur.', previous: $e);
        }

        $content = $response->json('candidates.0.content.parts.0.text');

        if ($content === null) {
            Log::warning('AiService::analyzeAts - réponse Gemini sans contenu exploitable', ['body' => $response->json()]);
            throw new AiServiceException('Réponse du service d\'analyse invalide.');
        }

        return trim($content);
    }
}
