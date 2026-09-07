use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // cv, cover_letter, invoice, quote, etc.
            $table->string('title');
            $table->string('template')->default('default');
            $table->jsonb('content'); // Données réactives (profil, exp, articles, etc.)
            $table->jsonb('style');   // Couleurs, polices, marges, tailles
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};