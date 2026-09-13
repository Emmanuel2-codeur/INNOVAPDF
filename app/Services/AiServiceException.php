<?php

namespace App\Services;

use Exception;
use Throwable;

/**
 * Levée quand un appel à un fournisseur IA (Groq, Gemini) échoue :
 * clé manquante, timeout, erreur HTTP, ou réponse inexploitable.
 */
class AiServiceException extends Exception
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
