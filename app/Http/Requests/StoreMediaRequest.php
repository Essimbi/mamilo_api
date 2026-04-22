<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:jpeg,png,webp,gif,svg,mp4,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip',
                'max:10240', // 10 MB
            ],
            'alt' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Un fichier est requis.',
            'file.file' => 'Le fichier doit être un fichier valide.',
            'file.mimes' => 'Le fichier doit être du type : jpeg, png, webp, gif, svg, mp4, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip.',
            'file.max' => 'La taille du fichier ne doit pas dépasser 10 MB.',
            'alt.string' => 'Le texte alternatif doit être une chaîne de caractères.',
            'alt.max' => 'Le texte alternatif ne doit pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne doit pas dépasser 1000 caractères.',
        ];
    }
}
