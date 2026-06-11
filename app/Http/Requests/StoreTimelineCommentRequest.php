<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimelineCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'isi_komentar' => ['required', 'string', 'max:500'],
            'media' => ['nullable', 'array', 'max:4'],
            'media.*' => ['file', 'max:102400'],
        ];
    }
}
