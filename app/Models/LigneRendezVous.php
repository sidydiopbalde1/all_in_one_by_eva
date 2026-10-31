<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneRendezVous extends Model
{
    public $timestamps = false;

    protected $table = 'rendez_vous_service';

    protected $fillable = ['rendez_vous_id', 'service_id', 'forfait_id', 'libelle', 'prix', 'duree_minutes'];

    protected function casts(): array
    {
        return ['prix' => 'integer', 'duree_minutes' => 'integer'];
    }

    public function rendezVous(): BelongsTo
    {
        return $this->belongsTo(RendezVous::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function forfait(): BelongsTo
    {
        return $this->belongsTo(Forfait::class);
    }
}
