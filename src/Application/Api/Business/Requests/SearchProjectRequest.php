<?php

namespace Application\Api\Business\Requests;

use Core\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SearchBusinessRequest extends BaseRequest
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
            'type' => ['required', 'string', 'in:passenger,sender'],
            'send_date' => ['nullable', 'date', 'after_or_equal:today'],
            'receive_date' => ['nullable', 'date', 'after_or_equal:send_date'],
            'path_type' => ['nullable', 'string', 'in:land,sea,air'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'min_weight' => ['nullable', 'integer', 'min:0'],
            'max_weight' => ['nullable', 'integer', 'min:0', 'gte:min_weight'],
            'query' => ['nullable', 'string', 'min:1', 'max:50'],
            'column' => ['nullable', 'string', 'min:2', 'max:50'],
            'sort' => ['nullable', 'string', 'in:desc,asc'],
            'page' => ['nullable','integer'],
            'count' => ['nullable','integer', 'min:5','max:200']
        ];
    }
}
