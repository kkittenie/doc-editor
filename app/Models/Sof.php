<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Menu S.O.F — entitas mandiri (tanpa relasi ke Customer/Document).
 * Segala data diisi langsung di form; berkas PDF di-upload secara mandiri.
 */
class Sof extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = ['draft', 'approved'];

    protected $fillable = [
        'user_id',
        'order_number',
        'customer_number',
        'customer_name',
        'contract_number',
        'contract_name',
        'active_date',
        'active_months',
        'finish_date',
        'total_value',
        'file_path',
        'file_name',
        'file_uploaded_at',
        'status',
        'revision_notes',
    ];

    protected $casts = [
        'active_date'         => 'date',
        'finish_date'         => 'date',
        'file_uploaded_at'    => 'datetime',
        'total_value'         => 'decimal:2',
        'revision_notes'      => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Label status untuk tampilan. */
    public function statusLabel(): string
    {
        return match (strtolower($this->status ?? 'draft')) {
            'draft'     => 'Draft',
            'approved'  => 'Disetujui',
            default     => ucfirst((string) $this->status),
        };
    }

    /** Sudah ada berkas PDF yang di-upload / discan. */
    public function hasFile(): bool
    {
        return ! empty($this->file_path);
    }

    /** Nama berkas saat diunduh. */
    public function fileName(): string
    {
        $name = trim((string) $this->file_name);

        if ($name !== '') {
            return $name;
        }

        return 'SOF-' . (Str::slug((string) $this->order_number) ?: 'document') . '.pdf';
    }
}
