<?php

namespace App\Models;

use App\Enums\TypeMouvementStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'mouvements_stock';

    protected $fillable = ['produit_id', 'type', 'quantite', 'stock_apres', 'motif', 'rendez_vous_id', 'enregistre_par'];

    protected function casts(): array
    {
        return ['type' => TypeMouvementStock::class, 'quantite' => 'decimal:2', 'stock_apres' => 'decimal:2'];
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }
}
