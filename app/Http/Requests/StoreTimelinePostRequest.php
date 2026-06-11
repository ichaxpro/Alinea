<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimelinePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul_buku_dibahas' => ['nullable', 'string', 'max:120'],
            'pesan' => ['required', 'string', 'max:500'],
            'tag' => ['nullable', 'string', 'max:30'],
            'media' => ['nullable', 'array', 'max:4'],
            'media.*' => ['file', 'max:102400'],
        ];
    }
}
