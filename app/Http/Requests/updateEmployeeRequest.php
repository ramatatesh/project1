<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateEmployeeRequest extends FormRequest
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
            'username'=>'nullable|string|max:255',
            'father_name'=>'nullable|string|max:255',
            'gender'=>'nullable|in:male,female',
            'phone'=>'nullable|string|min:10',
            'address'=>'nullable|string',
            'job'=>'nullable|string',
        ];
    }
}
