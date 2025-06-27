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
            'email' => 'nullable|email|unique:users,email,' . $this->userId,
            'username' => 'nullable|string|unique:users,username,' . $this->userId,
            'father_name'=>'sometimes|string|max:255',
            'mother_name'=>'sometimes|string|max:255',
            'birth_date'=>'sometimes|date|date_format:Y-m-d|before:today',
            'gender'=>'sometimes|in:male,female',
            'phone'=>'sometimes|string|min:10',
            'password'=>'sometimes|string|min:8',
            'grade'=>'sometimes|string',
            'address'=>'sometimes|string'
        ];
    }
}
