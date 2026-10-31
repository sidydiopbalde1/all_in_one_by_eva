<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Forfait extends Model
{
    protected $fillable = ['nom', 'description', 'prix', 'is_active'];

    protected function casts(): array
    {
        return ['prix' => 'integer', 'is_active' => 'boolean'];
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function dureeTotale(): int
    {
        return (int) $this->services->sum('duree_minutes');
    }

    public function prixDetail(): int
    {
        return (int) $this->services->sum('prix');
    }

    public function scopeActifs(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
