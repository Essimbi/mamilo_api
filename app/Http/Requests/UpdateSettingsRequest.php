<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:1000',
            'contact_email' => 'nullable|email',
            'social_media' => 'nullable|array',
            'social_media.*' => 'nullable|url',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'site_name.max' => 'Le nom du site ne doit pas dépasser 255 caractères.',
            'contact_email.email' => 'L\'email de contact doit être valide.',
            'social_media.*.url' => 'Les URLs des réseaux sociaux doivent être valides.',
        ];
    }
}
