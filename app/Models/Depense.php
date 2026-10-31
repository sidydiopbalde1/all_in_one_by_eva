<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $fillable = ['categorie_depense_id', 'montant', 'date_depense', 'description', 'justificatif_path', 'enregistre_par'];

    protected function casts(): array
    {
        return ['montant' => 'integer', 'date_depense' => 'date'];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieDepense::class, 'categorie_depense_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }
}
