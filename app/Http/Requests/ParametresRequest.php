<?php

namespace App\Http\Requests;

use App\Enums\TypeMessage;
use Illuminate\Foundation\Http\FormRequest;

class ParametresRequest extends FormRequest
{
    public function rules(): array
    {
        $regles = [
            'pause_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'fidelite_fcfa_par_point' => ['required', 'integer', 'min:1'],
            'inactivite_semaines' => ['required', 'integer', 'min:1', 'max:104'],
            'offre_anniversaire' => ['nullable', 'string', 'max:255'],
        ];

        foreach (TypeMessage::cases() as $type) {
            $regles["modeles.{$type->value}"] = ['required', 'string', 'max:1000'];
        }

        return $regles;
    }
}
