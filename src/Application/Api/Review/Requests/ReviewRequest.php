<?php

namespace Application\Api\Review\Requests;

use Core\Http\Requests\BaseRequest;

class ReviewRequest extends BaseRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rate' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'max:2000'],
            'services' => ['nullable', 'array'],
            'services.*' => ['nullable', 'integer', 'exists:services,id'],
            'files' => ['nullable', 'array'],
            'files.*.path' => ['required', 'string', 'max:2048'],
            'files.*.type' => ['nullable', 'string', 'in:image,video'],
            'status' => ['nullable', 'string', 'in:pending,cancelled'],
        ];
    }
}
