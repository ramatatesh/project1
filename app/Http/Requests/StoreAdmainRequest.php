<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdmainRequest extends FormRequest
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
            'username'=>'required|string|max:255',
            'specialization'=>'required|string',
            'gender'=>'required|in:male,female',
            'phone'=>'required|string|min:10',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|string|min:8',
            'grade'=>'required|string',
            'address'=>'required|string',
        ];
    }
}
