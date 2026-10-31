<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HistoriqueAction extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'historique_actions';

    protected $fillable = ['user_id', 'action', 'auditable_type', 'auditable_id', 'anciennes_valeurs', 'nouvelles_valeurs', 'ip'];

    protected function casts(): array
    {
        return ['anciennes_valeurs' => 'array', 'nouvelles_valeurs' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }
}
