<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    // Groq : rapide, pour les textes courts (correction, reformulation)
    public function correct(string $text): string
    {
        $response = Http::withToken(config('services.groq.key'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'openai/gpt-oss-20b',
                'messages' => [
                    ['role' => 'system', 'content' => 'Tu corriges l\'orthographe et la grammaire du texte fourni, sans changer le sens. Réponds uniquement avec le texte corrigé.'],
                    ['role' => 'user', 'content' => $text],
                ],
            ]);

        return $response->json('choices.0.message.content') ?? '';
    }

    // Gemini : grand contexte, pour comparer un CV entier à une offre
    public function analyzeAts(string $cvText, string $jobOffer): string
    {
        $prompt = "Compare ce CV à cette offre d'emploi. Liste les mots-clés présents et manquants, puis donne un score de compatibilité sur 100.\n\nCV:\n{$cvText}\n\nOffre:\n{$jobOffer}";

        $response = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' . config('services.gemini.key'),
            ['contents' => [['parts' => [['text' => $prompt]]]]]
        );

        return $response->json('candidates.0.content.parts.0.text') ?? '';
    }
}