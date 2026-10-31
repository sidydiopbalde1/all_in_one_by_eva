<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Enums\ZoneSalon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rules\Enum;

class RendezVousRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'employe_id' => ['required', 'exists:employes,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'zone' => ['required', new Enum(ZoneSalon::class)],
            'services' => ['array'],
            'services.*' => ['integer', 'exists:services,id'],
            'forfaits' => ['array'],
            'forfaits.*' => ['integer', 'exists:forfaits,id'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->hasAny(['date', 'heure_debut'])) {
                return;
            }

            $debut = Carbon::parse("{$this->input('date')} {$this->input('heure_debut')}");

            if ($debut->isPast() && ! $this->user()->can(Permission::RendezVousPasse->value)) {
                $validator->errors()->add('heure_debut', 'Impossible de créer un rendez-vous dans le passé.');
            }
        });
    }
}
