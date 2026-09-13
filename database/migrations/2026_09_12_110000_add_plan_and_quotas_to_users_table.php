<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Structure des plans/quotas (cahier des charges §17), sans paiement
            // réel branché pour l'instant : le passage à 'premium' se fait
            // manuellement en attendant l'intégration Mobile Money.
            $table->string('plan')->default('free')->after('password');
            $table->unsignedInteger('ai_calls_this_month')->default(0)->after('plan');
            $table->timestamp('quota_reset_at')->nullable()->after('ai_calls_this_month');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['plan', 'ai_calls_this_month', 'quota_reset_at']);
        });
    }
};
