<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employe extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'prenom', 'nom', 'telephone', 'fonction', 'specialites', 'taux_commission', 'is_active'];

    protected function casts(): array
    {
        return ['taux_commission' => 'decimal:2', 'is_active' => 'boolean'];
    }

    protected function nomComplet(): Attribute
    {
        return Attribute::get(fn () => "{$this->prenom} {$this->nom}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function disponibilites(): HasMany
    {
        return $this->hasMany(Disponibilite::class)->orderBy('jour_semaine');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class);
    }

    public function scopeActifs(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
