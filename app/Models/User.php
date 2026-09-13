<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Document;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- LIGNE AJOUTÉE ICI
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'quota_reset_at' => 'datetime',
        ];
    }
    
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** Limites du plan gratuit (cahier des charges §17 — structure sans paiement réel pour l'instant). */
    public const FREE_PLAN_LIMITS = [
        'documents' => 3,
        'ai_calls_per_month' => 20,
    ];

    public function isPremium(): bool
    {
        return $this->plan === 'premium';
    }

    /**
     * Vérifie et incrémente le quota d'appels IA mensuel. Renvoie false si le
     * quota du plan gratuit est dépassé (les comptes premium ne sont jamais limités).
     */
    public function consumeAiQuota(): bool
    {
        if ($this->isPremium()) {
            return true;
        }

        // Réinitialisation mensuelle automatique.
        if (! $this->quota_reset_at || $this->quota_reset_at->lt(now()->startOfMonth())) {
            $this->ai_calls_this_month = 0;
            $this->quota_reset_at = now();
        }

        if ($this->ai_calls_this_month >= self::FREE_PLAN_LIMITS['ai_calls_per_month']) {
            $this->save();
            return false;
        }

        $this->ai_calls_this_month++;
        $this->save();

        return true;
    }

    public function hasReachedDocumentLimit(): bool
    {
        if ($this->isPremium()) {
            return false;
        }

        return $this->documents()->count() >= self::FREE_PLAN_LIMITS['documents'];
    }
}