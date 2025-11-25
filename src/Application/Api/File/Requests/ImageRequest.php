<?php

namespace Application\Api\File\Requests;

use Core\Http\Requests\BaseRequest;

class ImageRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user && $user->status == 1; // Check user is active
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required','image','mimes:jpg,jpeg,png,gif,svg','max:51200']
        ];
    }
}
