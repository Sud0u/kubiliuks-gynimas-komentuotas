<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Įveskite vardą.',
            'name.min' => 'Vardas per trumpas.',
            'name.max' => 'Vardas per ilgas.',
            'email.required' => 'Įveskite el. paštą.',
            'email.email' => 'Neteisingas el. pašto formatas.',
            'email.max' => 'El. paštas per ilgas.',
            'email.unique' => 'Toks el. paštas jau naudojamas.',
        ];
    }
}