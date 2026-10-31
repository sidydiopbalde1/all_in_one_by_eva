<?php

namespace App\Http\Controllers;

use App\Enums\TypeMessage;
use App\Models\Client;
use App\Models\RendezVous;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

/**
 * Redirige vers WhatsApp (wa.me) avec un message prérempli.
 * L'envoi reste manuel : aucun statut « envoyé » n'est enregistré.
 */
class WhatsAppController extends Controller
{
    public function __construct(private readonly WhatsAppService $whatsApp) {}

    public function client(Client $client, TypeMessage $type): RedirectResponse
    {
        return $this->ouvrir($client, $this->whatsApp->messagePour($type, $client));
    }

    public function rappel(RendezVous $rendezVous): RedirectResponse
    {
        $rendezVous->load(['client', 'lignes']);

        $message = $this->whatsApp->messagePour(TypeMessage::RappelRendezVous, $rendezVous->client, [
            'date' => $rendezVous->debut->format('d/m/Y'),
            'heure' => $rendezVous->debut->format('H\hi'),
            'prestation' => $rendezVous->libellePrestations(),
        ]);

        return $this->ouvrir($rendezVous->client, $message);
    }

    private function ouvrir(Client $client, string $message): RedirectResponse
    {
        try {
            return redirect()->away($this->whatsApp->lien($client->numeroWhatsApp(), $message));
        } catch (InvalidArgumentException) {
            return back()->withErrors(['whatsapp' => 'Le numéro WhatsApp de ce client est invalide.']);
        }
    }
}
