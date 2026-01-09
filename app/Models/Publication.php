<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Publication extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'date',
        'content_idea_id',
        'shooting_id',
        'status',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relation : Une publication appartient à un client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation : Une publication utilise une idée de contenu
     */
    public function contentIdea(): BelongsTo
    {
        return $this->belongsTo(ContentIdea::class);
    }

    /**
     * Relation : Une publication peut être liée à un tournage (optionnel)
     */
    public function shooting(): BelongsTo
    {
        return $this->belongsTo(Shooting::class);
    }

    /**
     * Vérifie si la publication est en retard
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->date < now()->toDateString();
    }

    /**
     * Vérifie si la publication approche (dans les 3 prochains jours)
     */
    public function isUpcoming(): bool
    {
        $daysUntil = now()->diffInDays($this->date, false);
        return $this->status === 'pending' && $daysUntil >= 0 && $daysUntil <= 3;
    }

    /**
     * Vérifie si la publication est complétée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
