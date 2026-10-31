<?php

namespace App\Models;

use App\Enums\TypeRecompense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Recompense extends Model
{
    protected $fillable = ['libelle', 'type', 'seuil_points', 'valeur', 'is_active'];

    protected function casts(): array
    {
        return ['type' => TypeRecompense::class, 'seuil_points' => 'integer', 'valeur' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeActives(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
