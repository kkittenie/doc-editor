<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

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

    public function barang()
    {
        return $this->hasMany(Barang::class)->whereNull('document_id');
    }

    public function barangCopies()
    {
        return $this->hasMany(Barang::class)->whereNotNull('document_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class)->whereNull('document_id');
    }

    public function serviceCopies()
    {
        return $this->hasMany(Service::class)->whereNotNull('document_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function latestDocument()
    {
        return $this->hasOne(Document::class)->latestOfMany();
    }

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
