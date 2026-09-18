<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pelanggan / kontrak pelanggan (Tabel Pelanggan di halaman Dokumen Saya).
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /** Status alur kerja kontrak (dipakai juga oleh dokumen). */
    public const STATUSES = [
        'draft',
        'on_progress',
        'on_review',
        'revisi',
        'disetujui',
    ];

    protected $fillable = [
        'user_id',
        'customer_number',
        'name',
        'contract_number',
        'contract_name',
        'active_date',
        'active_months',
        'finish_date',
        'status',
    ];

    protected $casts = [
        'active_date'   => 'date',
        'finish_date'   => 'date',
        'active_months' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Data Barang (nullable, boleh banyak). */
    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    /** Data Service (nullable, boleh banyak). */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /** Label status untuk tampilan tabel. */
    public function statusLabel(): string
    {
        return match (strtolower($this->status ?? 'draft')) {
            'draft'       => 'Draft',
            'on_progress' => 'On Progress',
            'on_review'   => 'On Review',
            'revisi'      => 'Revisi',
            'disetujui'   => 'Disetujui',
            default       => ucfirst((string) $this->status),
        };
    }
}
