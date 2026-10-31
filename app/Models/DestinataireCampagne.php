<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinataireCampagne extends Model
{
    public $timestamps = false;

    protected $table = 'destinataires_campagnes';

    protected $fillable = ['campagne_message_id', 'client_id', 'message_personnalise', 'ouvert_le', 'suivi_manuel'];

    protected function casts(): array
    {
        return ['ouvert_le' => 'datetime'];
    }

    public function campagne(): BelongsTo
    {
        return $this->belongsTo(CampagneMessage::class, 'campagne_message_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
