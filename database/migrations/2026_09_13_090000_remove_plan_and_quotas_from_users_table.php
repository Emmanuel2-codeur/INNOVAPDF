<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Retrait de S8 (plans/quotas) : sur décision produit, aucune restriction
     * de paiement/quota n'est appliquée pour l'instant — tous les modèles et
     * fonctionnalités sont disponibles pour tous les comptes.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'plan')) {
                $table->dropColumn(['plan', 'ai_calls_this_month', 'quota_reset_at']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('plan')->default('free')->after('password');
            $table->unsignedInteger('ai_calls_this_month')->default(0)->after('plan');
            $table->timestamp('quota_reset_at')->nullable()->after('ai_calls_this_month');
        });
    }
};
