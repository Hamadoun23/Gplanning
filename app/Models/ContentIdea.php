<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentIdea extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'type',
    ];

    /**
     * Relation : Une idée de contenu peut être associée à plusieurs tournages
     */
    public function shootings(): BelongsToMany
    {
        return $this->belongsToMany(Shooting::class, 'content_idea_shooting')
            ->withTimestamps();
    }

    /**
     * Relation : Une idée de contenu peut être utilisée dans plusieurs publications
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }
}
