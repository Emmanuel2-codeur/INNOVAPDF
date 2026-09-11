<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

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