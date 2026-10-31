<?php

namespace App\Models;

use App\Enums\TypeMessage;
use Illuminate\Database\Eloquent\Model;

class ModeleMessage extends Model
{
    protected $table = 'modeles_messages';

    protected $fillable = ['type', 'titre', 'contenu'];

    protected function casts(): array
    {
        return ['type' => TypeMessage::class];
    }
}
