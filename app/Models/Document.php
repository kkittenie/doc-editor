<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

        /** Status dokumen — satu kosakata dengan alur kontrak pelanggan. */
    public const STATUSES = [
        'draft',
        'on_progress',
        'on_review',
        'revisi',
        'disetujui',
        'archived',
    ];

        protected $fillable = [
        'user_id',
        'customer_id',
        'title',
        'type',
        'header_data',
        'body_content',
        'footer_data',
        'signature_data',
        'status',
        'pdf_path',
        'pdf_generated_at',
    ];

    protected $casts = [
        'header_data'      => 'array',
        'body_content'     => 'array',
        'footer_data'      => 'array',
        'signature_data'   => 'array',
        'pdf_generated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Pelanggan/kontrak yang menjadi induk dokumen ini (opsional). */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /** Data barang yang menempel pada kontrak dokumen ini. */
    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    /** Data service yang menempel pada kontrak dokumen ini. */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /** Label status untuk tampilan. */
    public function statusLabel(): string
    {
        return match (strtolower($this->status ?? 'draft')) {
            'draft'       => 'Draft',
            'on_progress' => 'On Progress',
            'on_review'   => 'On Review',
            'revisi'      => 'Revisi',
            'disetujui'   => 'Disetujui',
            'archived'    => 'Diarsipkan',
            default       => ucfirst((string) $this->status),
        };
    }

    /**
     * Sudah disetujui (dokumen final yang boleh diterbitkan sebagai S.O.F).
     */
    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    /**
     * Punya berkas PDF S.O.F tersimpan? (path saja — keberadaan file dicek
     * oleh pemanggil lewat Storage supaya model tetap bebas dari I/O.)
     */
    public function hasSofPdf(): bool
    {
        return ! empty($this->pdf_path);
    }
}