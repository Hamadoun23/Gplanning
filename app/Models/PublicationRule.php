<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'day_of_week',
    ];

    /**
     * Relation : Une règle de publication appartient à un client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
