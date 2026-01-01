<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletTopupRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount' => ['required','numeric','min:1'],
            'method' => ['required', Rule::in(['credit_card','apple_pay','stc'])],
        ];
    }
}