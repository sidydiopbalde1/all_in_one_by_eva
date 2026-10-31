<?php

namespace App\Models;

use App\Enums\StatutClient;
use App\Enums\StatutRendezVous;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'prenom', 'nom', 'telephone', 'whatsapp', 'email', 'date_naissance', 'date_inscription',
        'preference_communication', 'consentement_promo', 'consentement_modifie_le',
        'preferences_prestations', 'notes', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_inscription' => 'date',
            'consentement_promo' => 'boolean',
            'consentement_modifie_le' => 'datetime',
            'statut' => StatutClient::class,
        ];
    }

    protected function nomComplet(): Attribute
    {
        return Attribute::get(fn () => "{$this->prenom} {$this->nom}");
    }

    public function rendezVous(): HasMany
    {
        return $this->hasMany(RendezVous::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function mouvementsPoints(): HasMany
    {
        return $this->hasMany(MouvementPoints::class);
    }

    public function anniversairesTraites(): HasMany
    {
        return $this->hasMany(AnniversaireTraite::class);
    }

    public function soldePoints(): int
    {
        return (int) $this->mouvementsPoints()->sum('points');
    }

    public function derniereVisite(): ?CarbonInterface
    {
        return $this->rendezVous()
            ->where('statut', StatutRendezVous::Termine)
            ->latest('debut')
            ->first()?->debut;
    }

    public function numeroWhatsApp(): string
    {
        return $this->whatsapp ?: $this->telephone;
    }

    public function scopeRecherche(Builder $query, ?string $terme): void
    {
        $terme = trim((string) $terme);

        if ($terme === '') {
            return;
        }

        $chiffres = preg_replace('/\D/', '', $terme);

        $query->where(function (Builder $q) use ($terme, $chiffres) {
            $q->where('prenom', 'like', "%{$terme}%")
                ->orWhere('nom', 'like', "%{$terme}%");

            if ($chiffres !== '') {
                $q->orWhere('telephone', 'like', "%{$chiffres}%")
                    ->orWhere('whatsapp', 'like', "%{$chiffres}%");
            }
        });
    }

    public function scopeNonArchives(Builder $query): void
    {
        $query->where('statut', '!=', StatutClient::Archive);
    }

    /** Clients dont la dernière prestation terminée date de plus de N semaines. */
    public function scopeInactifsDepuis(Builder $query, int $semaines): void
    {
        $limite = now()->subWeeks($semaines);

        $query->nonArchives()
            ->whereHas('rendezVous', fn (Builder $q) => $q->where('statut', StatutRendezVous::Termine))
            ->whereDoesntHave('rendezVous', fn (Builder $q) => $q
                ->where('statut', StatutRendezVous::Termine)
                ->where('debut', '>=', $limite));
    }
}
