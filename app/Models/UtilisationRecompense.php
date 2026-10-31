<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtilisationRecompense extends Model
{
    public $timestamps = false;

    protected $table = 'utilisations_recompenses';

    protected $fillable = ['client_id', 'recompense_id', 'mouvement_points_id', 'enregistre_par', 'utilisee_le'];

    protected function casts(): array
    {
        return ['utilisee_le' => 'datetime'];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function recompense(): BelongsTo
    {
        return $this->belongsTo(Recompense::class);
    }
}
