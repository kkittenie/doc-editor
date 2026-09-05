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
        'title',
        'type',
        'header_data',
        'body_content',
        'footer_data',
        'signature_data',
        'revision_notes',
        'status',
    ];

    protected $casts = [
        'header_data'    => 'array',
        'body_content'   => 'array',
        'footer_data'    => 'array',
        'signature_data' => 'array',
        'revision_notes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}