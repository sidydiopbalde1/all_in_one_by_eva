<?php

namespace App\Models;

use App\Enums\StatutRendezVous;
use App\Enums\ZoneSalon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $fillable = ['client_id', 'employe_id', 'debut', 'fin', 'zone', 'prix_prevu', 'statut', 'commentaire', 'cree_par'];

    protected function casts(): array
    {
        return [
            'debut' => 'datetime',
            'fin' => 'datetime',
            'prix_prevu' => 'integer',
            'zone' => ZoneSalon::class,
            'statut' => StatutRendezVous::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneRendezVous::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function montantPaye(): int
    {
        return (int) $this->paiements()->valides()->sum('montant_paye');
    }

    public function resteAPayer(): int
    {
        return max(0, $this->prix_prevu - $this->montantPaye());
    }

    public function estSolde(): bool
    {
        return $this->resteAPayer() === 0;
    }

    public function libellePrestations(): string
    {
        return $this->lignes->pluck('libelle')->join(', ');
    }

    public function scopeBloquants(Builder $query): void
    {
        $query->whereIn('statut', StatutRendezVous::bloquants());
    }

    public function scopeEntre(Builder $query, $debut, $fin): void
    {
        $query->where('debut', '>=', $debut)->where('debut', '<', $fin);
    }
}
