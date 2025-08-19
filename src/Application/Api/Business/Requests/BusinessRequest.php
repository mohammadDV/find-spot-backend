<?php

namespace Application\Api\Business\Requests;

use Core\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class BusinessRequest extends BaseRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'lat' => ['required', 'string'],
            'long' => ['required', 'string'],
            'website' => ['nullable', 'url'],
            'facebook' => ['nullable', 'url'],
            'instagram' => ['nullable', 'url'],
            'youtube' => ['nullable', 'url'],
            'tiktok' => ['nullable', 'url'],
            'whatsapp' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'address' => ['nullable', 'string'],
            'start_amount' => ['required', 'numeric', 'min:0'],
            'amount_type' => ['nullable', 'integer', 'in:1,2,3,4'],
            'image' => ['nullable', 'string', 'max:2048'],
            'menu_image' => ['nullable', 'string', 'max:2048'],
            'video' => ['nullable', 'string', 'max:2048'],
            'from_monday' => ['nullable', 'integer', 'between:0,23'],
            'from_tuesday' => ['nullable', 'integer', 'between:0,23'],
            'from_wednesday' => ['nullable', 'integer', 'between:0,23'],
            'from_thursday' => ['nullable', 'integer', 'between:0,23'],
            'from_friday' => ['nullable', 'integer', 'between:0,23'],
            'from_saturday' => ['nullable', 'integer', 'between:0,23'],
            'from_sunday' => ['nullable', 'integer', 'between:0,23'],
            'to_monday' => ['nullable', 'integer', 'between:0,23'],
            'to_tuesday' => ['nullable', 'integer', 'between:0,23'],
            'to_wednesday' => ['nullable', 'integer', 'between:0,23'],
            'to_thursday' => ['nullable', 'integer', 'between:0,23'],
            'to_friday' => ['nullable', 'integer', 'between:0,23'],
            'to_saturday' => ['nullable', 'integer', 'between:0,23'],
            'to_sunday' => ['nullable', 'integer', 'between:0,23'],
            'active' => ['nullable', 'integer', 'in:0,1'],
            'status' => ['nullable', 'string', 'in:pending,approved'],
            'country_id' => ['required', 'exists:countries,id'],
            'city_id' => ['required', 'exists:cities,id'],
            'area_id' => ['required', 'exists:areas,id'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:categories,id'],
            'services' => ['nullable', 'array'],
            'services.*' => ['exists:services,id'],
            'tags' => ['nullable', 'array'],
            'tags.*.title' => ['required', 'string', 'max:255'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['exists:facilities,id'],
            'filters' => ['nullable', 'array'],
            'filters.*' => ['exists:filters,id'],
            'files' => ['nullable', 'array'],
            'files.*.path' => ['required', 'string', 'max:2048'],
            'files.*.type' => ['nullable', 'string', 'in:image,video,document']
        ];
    }
}
