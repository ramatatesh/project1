<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('userId'); // تأكد أن اسم الباراميتر مطابق للراوت

        return [
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'username' => [
                'nullable',
                'string',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'specialization' => 'nullable|string',
            'gender' => 'nullable|in:male,female',
            'phone' => 'nullable|string|min:10',
            'password' => 'nullable|string|min:8',
            'grade' => 'nullable|string',
            'address' => 'nullable|string',
            'start_date' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'nationality' => 'nullable|string',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
        ];
    }
}
