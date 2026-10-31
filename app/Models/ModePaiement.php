<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModePaiement extends Model
{
    protected $table = 'modes_paiement';

    protected $fillable = ['libelle', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function scopeActifs(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
