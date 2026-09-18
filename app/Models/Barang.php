<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Data Barang milik pelanggan (tabel `barang`, boleh kosong).
 */
class Barang extends Model
{
    use HasFactory;

    /** Nama tabel mengikuti istilah di alur kerja (bukan "barangs"). */
    protected $table = 'barang';

    protected $fillable = [
        'customer_id',
        'document_id',
        'name',
        'quantity',
        'price',
    ];

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
