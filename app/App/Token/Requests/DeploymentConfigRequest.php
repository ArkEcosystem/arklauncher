<?php

declare(strict_types=1);

namespace App\Token\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DeploymentConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['file', 'required', 'mimetypes:application/zip', 'min:1', 'max:50'],
        ];
    }
}
