<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 */
class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'customer_id',
        'document_id',
        'name',
        'quantity',
        'price',
        'price_type',
        'ownership',
    ];

    public const PRICE_TYPES = ['one_time', 'monthly'];

    public const OWNERSHIPS = ['disewa', 'dipinjamkan', 'dibeli'];

    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'decimal:2',
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
