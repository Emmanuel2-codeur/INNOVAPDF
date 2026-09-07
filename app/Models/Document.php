namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'template',
        'content',
        'style',
        'status',
    ];

    protected $casts = [
        'content' => 'array',
        'style' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}