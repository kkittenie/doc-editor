<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 */
class Service extends Model
{
    use HasFactory;

    protected $table = 'service';

    protected $fillable = [
        'customer_id',
        'document_id',
        'name',
        'price',
        'price_type',
    ];

    public const PRICE_TYPES = ['one_time', 'monthly'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}
