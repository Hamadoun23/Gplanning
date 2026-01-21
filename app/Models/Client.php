<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_entreprise',
    ];


    /**
     * Relation : Un client a plusieurs règles de publication
     */
    public function publicationRules(): HasMany
    {
        return $this->hasMany(PublicationRule::class);
    }

    /**
     * Relation : Un client a plusieurs tournages
     */
    public function shootings(): HasMany
    {
        return $this->hasMany(Shooting::class);
    }

    /**
     * Relation : Un client a plusieurs publications
     */
    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Relation : Un client a plusieurs rapports
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ClientReport::class);
    }

    /**
     * Vérifie si un jour de la semaine est non recommandé pour ce client
     */
    public function isDayNotRecommended(string $dayOfWeek): bool
    {
        return $this->publicationRules()
            ->where('day_of_week', $dayOfWeek)
            ->exists();
    }
}
