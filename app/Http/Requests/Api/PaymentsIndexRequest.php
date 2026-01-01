<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentsIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['pending','paid','failed','cancelled','refunded'])],
            'method' => ['nullable', Rule::in(['wallet','credit_card','apple_pay','google_pay','cash'])],
            'invoice_id' => ['nullable', 'integer'],
        ];
    }
}