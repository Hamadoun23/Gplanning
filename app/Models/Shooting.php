<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shooting extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'date',
        'status',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relation : Un tournage appartient à un client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation : Un tournage peut être associé à plusieurs idées de contenu
     */
    public function contentIdeas(): BelongsToMany
    {
        return $this->belongsToMany(ContentIdea::class, 'content_idea_shooting')
            ->withTimestamps();
    }

    /**
     * Relation : Un tournage peut être lié à plusieurs publications
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Vérifie si le tournage est en retard
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->date < now()->toDateString();
    }

    /**
     * Vérifie si le tournage approche (dans les 3 prochains jours)
     */
    public function isUpcoming(): bool
    {
        $daysUntil = now()->diffInDays($this->date, false);
        return $this->status === 'pending' && $daysUntil >= 0 && $daysUntil <= 3;
    }

    /**
     * Vérifie si le tournage est complété
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
