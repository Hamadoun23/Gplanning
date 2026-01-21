<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'report_type',
        'report_date',
        'file_path',
        'original_filename',
        'file_size',
        'uploaded_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'uploaded_at' => 'datetime',
    ];

    /**
     * Relation : Un rapport appartient à un client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Obtenir le type de rapport en français
     */
    public function getReportTypeLabelAttribute(): string
    {
        return $this->report_type === 'monthly' ? 'Mensuel' : 'Annuel';
    }

    /**
     * Obtenir l'URL du fichier
     */
    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
