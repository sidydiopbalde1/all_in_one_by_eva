<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disponibilite extends Model
{
    public const JOURS = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];

    protected $fillable = ['employe_id', 'jour_semaine', 'heure_debut', 'heure_fin'];

    protected function casts(): array
    {
        return ['jour_semaine' => 'integer'];
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function libelleJour(): string
    {
        return self::JOURS[$this->jour_semaine];
    }
}
