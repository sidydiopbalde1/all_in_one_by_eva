<?php

namespace App\Models;

use App\Enums\ZoneSalon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['categorie_service_id', 'nom', 'description', 'prix', 'duree_minutes', 'zone', 'is_active'];

    protected function casts(): array
    {
        return [
            'prix' => 'integer',
            'duree_minutes' => 'integer',
            'zone' => ZoneSalon::class,
            'is_active' => 'boolean',
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieService::class, 'categorie_service_id');
    }

    public function employes(): BelongsToMany
    {
        return $this->belongsToMany(Employe::class);
    }

    public function forfaits(): BelongsToMany
    {
        return $this->belongsToMany(Forfait::class);
    }

    public function produits(): BelongsToMany
    {
        return $this->belongsToMany(Produit::class)->withPivot('quantite');
    }

    public function scopeActifs(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
