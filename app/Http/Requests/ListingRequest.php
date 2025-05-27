<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use App\Rules\RequiredWithDropdownValueRule;

class ListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'PUT':
                $title                  = 'required';
                $description            = 'nullable';
                // $screen_image           = 'nullable|mimes:png,jpg,jpeg';
                // $audio_display_image    = 'nullable|mimes:png,jpg,jpeg';
                break;

            default:
                $title                  = 'required';
                $description            = 'nullable';
                break;
        }

        return [
            'title'                 => $title,
            'description'           => $description,
        ];
    }

    public function messages()
    {
        return [
            'title.required'                           => 'The title field(in english) is required.',
            'description.required'                     => 'The description field(in english) is required.',];
    }
}
