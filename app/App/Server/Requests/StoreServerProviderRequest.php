<?php

declare(strict_types=1);

namespace App\Server\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreServerProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type'         => ['required', 'max:255'],
            'name'         => ['required', 'max:255'],
            'access_token' => ['required', 'max:255'],
            'access_key'   => 'required_if:type,===,aws',
        ];
    }
}
