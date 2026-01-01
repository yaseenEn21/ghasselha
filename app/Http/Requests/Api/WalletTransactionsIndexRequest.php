<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WalletTransactionsIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'archived' => ['nullable', Rule::in(['0','1'])],
            'direction' => ['nullable', Rule::in(['credit','debit'])],
            'type' => ['nullable', Rule::in(['topup','refund','adjustment','booking_charge','package_purchase'])],
            'status' => ['nullable', Rule::in(['posted','reversed'])],
        ];
    }
}