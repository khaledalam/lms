<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:150',
            'content' => 'nullable|string',
            'attachment' => 'sometimes|nullable|file|max:10240', // 10MB limit
            'remove_attachment' => 'sometimes|boolean',
        ];
    }
}
