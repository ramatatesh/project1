<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'username'=>'required|string|max:255',
            'father_name'=>'required|string|max:255',
            'gender'=>'required|in:male,female',
            'phone'=>'required|string|min:10',
            'address'=>'required|string',
            'job'=>'required|string',
        ];
    }
}
