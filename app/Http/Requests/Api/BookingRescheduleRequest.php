<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BookingRescheduleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'date' => ['required','date_format:d-m-Y'],
            'time' => ['required','regex:/^\d{2}:\d{2}$/'],
            'employee_id' => ['nullable','integer','exists:employees,id'], // اختياري
        ];
    }
}
