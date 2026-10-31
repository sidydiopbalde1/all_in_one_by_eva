<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnniversaireTraite extends Model
{
    protected $table = 'anniversaires_traites';

    protected $fillable = ['client_id', 'annee', 'offre', 'traite_par'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
