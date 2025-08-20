<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class updateAdmainRequest extends FormRequest
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
        $userId = $this->route('userId');
        return [
            'email' => 'nullable|email|unique:users,email,'  . $userId,
            'username' => 'nullable|string|unique:users,username,'  . $userId,
            'first_name'=>'nullable|string|max:255',
            'last_name'=>'nullable|string|max:255',
            'father_name'=>'nullable|string|max:255',
            'mother_name'=>'nullable|string|max:255',
            'birth_date'=>'nullable|date',
            'nationality'=>'nullable|string|max:255',
            'specialization'=>'nullable|string',
            'gender'=>'nullable|in:male,female',
            'phone'=>'nullable|string|min:10',
            'password'=>'nullable|string|min:8',
            'grade'=>'nullable|string',
            'address'=>'nullable|string',
        ];
    }
}
