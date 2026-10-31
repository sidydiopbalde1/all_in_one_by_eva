<?php

namespace App\Http\Requests;

use App\Enums\StatutClient;
use App\Models\Client;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ClientRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var Client|null $client */
        $client = $this->route('client');

        return [
            'prenom' => ['required', 'string', 'max:80'],
            'nom' => ['required', 'string', 'max:80'],
            'telephone' => ['required', 'string', 'max:20', Rule::unique('clients')->ignore($client)],
            'whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'date_naissance' => ['nullable', 'date', 'before:today'],
            'preference_communication' => ['required', Rule::in(['whatsapp', 'appel', 'sms', 'email'])],
            'consentement_promo' => ['boolean'],
            'preferences_prestations' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'statut' => ['sometimes', new Enum(StatutClient::class)],
        ];
    }

    public function messages(): array
    {
        return ['telephone.unique' => 'Un client existe déjà avec ce numéro de téléphone.'];
    }

    /** Les numéros sont stockés au format international pour détecter les doublons quel que soit le format saisi. */
    protected function prepareForValidation(): void
    {
        $whatsapp = app(WhatsAppService::class);

        $this->merge([
            'consentement_promo' => $this->boolean('consentement_promo'),
            'telephone' => $this->normaliser($whatsapp, $this->input('telephone')),
            'whatsapp' => $this->normaliser($whatsapp, $this->input('whatsapp')),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $service = app(WhatsAppService::class);
            foreach (['telephone', 'whatsapp'] as $champ) {
                $valeur = $this->input($champ);
                if ($valeur && ! $service->numeroValide($valeur)) {
                    $validator->errors()->add($champ, 'Numéro invalide (ex. 77 123 45 67 ou +221 77 123 45 67).');
                }
            }
        });
    }

    private function normaliser(WhatsAppService $service, ?string $numero): ?string
    {
        if (! filled($numero)) {
            return null;
        }

        return $service->numeroValide($numero) ? $service->normaliserNumero($numero) : $numero;
    }
}
