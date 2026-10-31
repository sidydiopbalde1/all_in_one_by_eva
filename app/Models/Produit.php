<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Produit extends Model
{
    protected $fillable = ['nom', 'categorie', 'quantite', 'unite', 'prix_achat', 'prix_vente', 'seuil_alerte', 'fournisseur', 'date_entree'];

    protected function casts(): array
    {
        return [
            'quantite' => 'decimal:2',
            'seuil_alerte' => 'decimal:2',
            'prix_achat' => 'integer',
            'prix_vente' => 'integer',
            'date_entree' => 'date',
        ];
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementStock::class)->latest('created_at');
    }

    public function estEnAlerte(): bool
    {
        return (float) $this->quantite <= (float) $this->seuil_alerte;
    }

    public function scopeEnAlerte(Builder $query): void
    {
        $query->whereColumn('quantite', '<=', 'seuil_alerte');
    }
}
