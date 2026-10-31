<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'prenom' => ['required', 'string', 'max:80'],
            'nom' => ['required', 'string', 'max:80'],
            'telephone' => ['required', 'string', 'max:20'],
            'fonction' => ['required', 'string', 'max:80'],
            'specialites' => ['nullable', 'string', 'max:255'],
            'taux_commission' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
            'services' => ['array'],
            'services.*' => ['integer', 'exists:services,id'],
            'horaires' => ['array'],
            'horaires.*.actif' => ['boolean'],
            'horaires.*.debut' => ['nullable', 'required_if_accepted:horaires.*.actif', 'date_format:H:i'],
            'horaires.*.fin' => ['nullable', 'required_if_accepted:horaires.*.actif', 'date_format:H:i'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('horaires', []) as $jour => $h) {
                if (($h['actif'] ?? false) && filled($h['debut'] ?? null) && filled($h['fin'] ?? null) && $h['fin'] <= $h['debut']) {
                    $validator->errors()->add("horaires.{$jour}.fin", 'L\'heure de fin doit être après l\'heure de début.');
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $horaires = collect($this->input('horaires', []))
            ->map(fn ($h) => [...$h, 'actif' => filter_var($h['actif'] ?? false, FILTER_VALIDATE_BOOLEAN)])
            ->all();

        $this->merge(['is_active' => $this->boolean('is_active'), 'horaires' => $horaires]);
    }

    /** @return array<int, array{jour_semaine:int, heure_debut:string, heure_fin:string}> */
    public function disponibilites(): array
    {
        return collect($this->validated('horaires', []))
            ->filter(fn ($h) => $h['actif'])
            ->map(fn ($h, $jour) => ['jour_semaine' => (int) $jour, 'heure_debut' => $h['debut'], 'heure_fin' => $h['fin']])
            ->values()
            ->all();
    }
}
