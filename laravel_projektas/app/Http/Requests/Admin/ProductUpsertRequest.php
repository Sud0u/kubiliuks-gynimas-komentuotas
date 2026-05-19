<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpsertRequest extends FormRequest
{
    // GYNIMO PAAISKINIMAS PRADZIA: leidimas admin formai
    // Cia grazinama true, nes leidimai siame projekte valdomi per route/middleware.
    // Tai reiskia, kad i sita forma jau turi patekti tik admin.
    // GYNIMO PAAISKINIMAS PABAIGA: leidimas admin formai
    public function authorize(): bool
    {
        return auth()->check() && (bool) auth()->user()->is_admin;
    }

    // GYNIMO PAAISKINIMAS PRADZIA: duomenu paruosimas pries validacija
    // Cia kai kurie formos checkbox arba skaiciai sutvarkomi pries rules tikrinima.
    // Taip validacija gauna tvarkingesnes reiksmes.
    // GYNIMO PAAISKINIMAS PABAIGA: duomenu paruosimas pries validacija
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'slug' => $this->input('slug') !== null
                ? trim((string) $this->input('slug'))
                : null,
            'description' => $this->input('description') !== null
                ? trim((string) $this->input('description'))
                : null,
            'production_time' => $this->input('production_time') !== null
                ? trim((string) $this->input('production_time'))
                : null,
            'price' => $this->input('price') !== null
                ? str_replace(',', '.', (string) $this->input('price'))
                : null,
            'stock' => $this->input('stock') !== null
                ? (int) $this->input('stock')
                : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    // GYNIMO PAAISKINIMAS PRADZIA: admin prekes validacijos taisykles
    // Cia aprasyta ka admin privalo uzpildyti kuriant arba redaguojant preke.
    // Pvz pavadinimas privalomas, kaina turi buti skaicius, nuotraukos turi buti paveiksleliai.
    // GYNIMO PAAISKINIMAS PABAIGA: admin prekes validacijos taisykles
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'production_time' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],

            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image_3' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_gallery_images' => ['nullable', 'array'],
            'remove_gallery_images.*' => ['nullable', 'string', 'max:500'],
        ];
    }

    // GYNIMO PAAISKINIMAS PRADZIA: lietuviski validacijos pranesimai
    // Cia surasyti pranesimai, kuriuos admin mato jei forma uzpildyta blogai.
    // Taip vartotojas nemato Laravel anglisku klaidu.
    // GYNIMO PAAISKINIMAS PABAIGA: lietuviski validacijos pranesimai
    public function messages(): array
    {
        return [
            'category_id.required' => 'Pasirinkite kategoriją.',
            'category_id.integer' => 'Kategorija parinkta neteisingai.',
            'category_id.exists' => 'Pasirinkta kategorija neegzistuoja.',

            'name.required' => 'Įveskite prekės pavadinimą.',
            'name.string' => 'Prekės pavadinimas turi būti tekstas.',
            'name.min' => 'Prekės pavadinimas per trumpas.',
            'name.max' => 'Prekės pavadinimas per ilgas.',

            'slug.string' => 'Slug reikšmė turi būti tekstas.',
            'slug.max' => 'Slug reikšmė per ilga.',

            'description.string' => 'Aprašymas turi būti tekstas.',
            'description.max' => 'Aprašymas per ilgas.',

            'production_time.string' => 'Pagaminimo termino laukas turi būti tekstas.',
            'production_time.max' => 'Pagaminimo termino tekstas per ilgas.',

            'price.required' => 'Įveskite kainą.',
            'price.numeric' => 'Kaina turi būti skaičius.',
            'price.min' => 'Kaina negali būti neigiama.',
            'price.max' => 'Kaina per didelė.',

            'stock.required' => 'Įveskite likutį.',
            'stock.integer' => 'Likutis turi būti sveikas skaičius.',
            'stock.min' => 'Likutis negali būti neigiamas.',
            'stock.max' => 'Likutis per didelis.',

            'image.image' => 'Pirmas paveikslėlis turi būti nuotrauka.',
            'image.mimes' => 'Pirmas paveikslėlis turi būti JPG, JPEG, PNG arba WEBP formato.',
            'image.max' => 'Pirmas paveikslėlis per didelis. Didžiausias dydis: 4 MB.',

            'image_2.image' => 'Antras paveikslėlis turi būti nuotrauka.',
            'image_2.mimes' => 'Antras paveikslėlis turi būti JPG, JPEG, PNG arba WEBP formato.',
            'image_2.max' => 'Antras paveikslėlis per didelis. Didžiausias dydis: 4 MB.',

            'image_3.image' => 'Trečias paveikslėlis turi būti nuotrauka.',
            'image_3.mimes' => 'Trečias paveikslėlis turi būti JPG, JPEG, PNG arba WEBP formato.',
            'image_3.max' => 'Trečias paveikslėlis per didelis. Didžiausias dydis: 4 MB.',

            'gallery_images.array' => 'Papildomos nuotraukos parinktos neteisingai.',
            'gallery_images.*.image' => 'Papildoma nuotrauka turi būti paveikslėlis.',
            'gallery_images.*.mimes' => 'Papildomos nuotraukos turi būti JPG, JPEG, PNG arba WEBP formato.',
            'gallery_images.*.max' => 'Papildoma nuotrauka per didelė. Didžiausias dydis: 4 MB.',
        ];
    }
}
