<?php

namespace App\Http\Requests\Dashboard\Packages;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return (new StorePackageRequest())->rules();
    }
}