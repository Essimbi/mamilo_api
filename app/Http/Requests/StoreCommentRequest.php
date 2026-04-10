<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'author_name' => 'required|string|max:255',
            'author_avatar' => 'nullable|url|max:2048',
            'content' => 'required|string|min:5|max:5000',
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'author_name.required' => 'Le nom de l\'auteur est obligatoire.',
            'author_name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'author_avatar.url' => 'L\'URL de l\'avatar n\'est pas valide.',
            'content.required' => 'Le commentaire ne peut pas être vide.',
            'content.min' => 'Le commentaire doit contenir au moins 5 caractères.',
            'content.max' => 'Le commentaire ne doit pas dépasser 5000 caractères.',
        ];
    }
}
