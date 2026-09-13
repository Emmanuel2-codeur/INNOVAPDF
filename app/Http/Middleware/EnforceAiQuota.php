<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnforceAiQuota
{
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->consumeAiQuota()) {
            return response()->json([
                'message' => 'Quota IA mensuel atteint (' . User::FREE_PLAN_LIMITS['ai_calls_per_month'] . ' appels/mois sur le plan gratuit). Passe à Premium pour un usage illimité.',
            ], 429);
        }

        return $next($request);
    }
}
