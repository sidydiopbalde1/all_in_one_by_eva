<?php

namespace App\Models;

use App\Enums\TypeMouvementPoints;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementPoints extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'mouvements_points';

    protected $fillable = ['client_id', 'paiement_id', 'type', 'points', 'motif', 'enregistre_par'];

    protected function casts(): array
    {
        return ['type' => TypeMouvementPoints::class, 'points' => 'integer'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function paiement(): BelongsTo
    {
        return $this->belongsTo(Paiement::class);
    }
}
