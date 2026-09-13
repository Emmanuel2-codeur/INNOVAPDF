<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Lien de partage non listé : accessible uniquement à qui possède le lien
            // (cahier des charges §10-§11 : "lien privé de partage", "URL partageable",
            // "contrôle de confidentialité"). NULL = partage désactivé.
            $table->string('share_token', 36)->nullable()->unique()->after('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('share_token');
        });
    }
};
