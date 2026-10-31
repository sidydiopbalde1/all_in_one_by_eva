<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'rendez_vous_id', 'mode_paiement_id', 'montant_total', 'montant_paye', 'reste_a_payer',
        'paye_le', 'reference', 'enregistre_par', 'annule_le', 'annule_par', 'motif_annulation',
    ];

    protected function casts(): array
    {
        return [
            'montant_total' => 'integer',
            'montant_paye' => 'integer',
            'reste_a_payer' => 'integer',
            'paye_le' => 'datetime',
            'annule_le' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(RendezVous::class);
    }

    public function mode(): BelongsTo
    {
        return $this->belongsTo(ModePaiement::class, 'mode_paiement_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }

    public function estAnnule(): bool
    {
        return $this->annule_le !== null;
    }

    /** Seuls les paiements non annulés comptent dans les recettes (pas de double comptage). */
    public function scopeValides(Builder $query): void
    {
        $query->whereNull('annule_le');
    }
}
