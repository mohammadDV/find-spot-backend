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
            'category' => ['nullable', 'exists:categories,id'],
            'query' => ['nullable', 'string', 'min:1', 'max:50'],
            'filters' => ['nullable', 'array'],
            'filters.*' => ['nullable', 'exists:filters,id'],
            'amount_type' => ['nullable', 'integer', 'in:1,2,3,4'],
            'now' => ['nullable', 'boolean'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'column' => ['nullable', 'string', 'min:2', 'max:50'],
            'sort' => ['nullable', 'string', 'in:desc,asc'],
            'page' => ['nullable','integer'],
            'count' => ['nullable','integer', 'min:5','max:200']
        ];
    }
}