<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampagneMessage extends Model
{
    protected $table = 'campagnes_messages';

    protected $fillable = ['titre', 'modele_message_id', 'message', 'statut_suivi', 'cree_par'];

    public function modele(): BelongsTo
    {
        return $this->belongsTo(ModeleMessage::class, 'modele_message_id');
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(DestinataireCampagne::class);
    }
}
