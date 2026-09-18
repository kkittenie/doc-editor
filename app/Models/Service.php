<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Data Service milik pelanggan (tabel `service`, boleh kosong).
 */
class Service extends Model
{
    use HasFactory;

    /** Nama tabel mengikuti istilah di alur kerja (bukan "services"). */
    protected $table = 'service';

    protected $fillable = [
        'customer_id',
        'document_id',
        'name',
        'price',
    ];

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
