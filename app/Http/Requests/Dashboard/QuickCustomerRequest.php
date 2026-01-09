<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class QuickCustomerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:190'],
            'mobile' => ['required','string','max:30', 'unique:users,mobile'], // ✅ عدّل لو اسم العمود مختلف
        ];
    }
}