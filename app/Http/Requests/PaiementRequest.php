<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaiementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mode_paiement_id' => ['required', Rule::exists('modes_paiement', 'id')->where('is_active', true)],
            'montant' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Accepte « 15 000 » ou « 15000 ».
        $this->merge(['montant' => preg_replace('/\D/', '', (string) $this->input('montant'))]);
    }
}
