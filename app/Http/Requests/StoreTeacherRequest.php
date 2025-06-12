<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
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
            'first_name'=>'required|string|max:255',
            'last_name'=>'required|string|max:255',
            'father_name'=>'required|string|max:255',
            'mother_name'=>'required|string|max:255',
            'specialization'=>'required|string',
            'gender'=>'required|in:male,female',
            'phone'=>'required|string|min:10',
            'email'=>'required|string|unique:users,email',
            'password'=>'required|string|min:8',
            'address'=>'required|string',
            'start_date'=>'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'birth_date'=>'required|date',
            'nationality'=>'required|string',

        ];
    }
}
