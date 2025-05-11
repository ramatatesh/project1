<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username'=>'sometimes|string|max:255',
            'father_name'=>'sometimes|string|max:255',
            'mother_name'=>'sometimes|string|max:255',
            'birth_date'=>'sometimes|date|date_format:Y-m-d|before:today',
            'gender'=>'sometimes|in:male,female',
            'phone'=>'sometimes|string|min:10',
            'email'=>'sometimes|string|email|unique:users,email',
            'password'=>'sometimes|string|min:8',
            'grade'=>'sometimes|string',
            'address'=>'sometimes|string'
        ];
    }
}
