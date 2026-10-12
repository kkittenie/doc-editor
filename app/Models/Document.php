<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

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
        'final_file_path',
        'final_file_name',
        'final_file_uploaded_at',
    ];

    protected $casts = [
        'header_data'           => 'array',
        'body_content'          => 'array',
        'footer_data'           => 'array',
        'signature_data'        => 'array',
        'pdf_generated_at'      => 'datetime',
        'final_file_uploaded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function barang()
    {
        return $this->hasMany(Barang::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

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
     */
    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }

    /**
     */
    public function hasSofPdf(): bool
    {
        return ! empty($this->pdf_path);
    }

    /**
     */
    public function hasFinalFile(): bool
    {
        return ! empty($this->final_file_path);
    }

    /**
     */
    public function finalFileName(): string
    {
        $name = trim((string) $this->final_file_name);

        if ($name !== '') {
            return $name;
        }

        return 'Kontrak-' . (Str::slug((string) $this->title) ?: 'dokumen') . '.pdf';
    }
}