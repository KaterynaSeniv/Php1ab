<?php

declare(strict_types=1);

namespace App\Http\Requests\Service;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Service::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:services,name'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Поле "Назва послуги" обов\'язкове.',
            'name.min' => 'Назва має містити мінімум 2 символи.',
            'name.unique' => 'Послуга з такою назвою вже існує.',
            'price.required' => 'Поле "Вартість" обов\'язкове.',
            'price.numeric' => 'Вартість має бути числом.',
            'price.min' => 'Вартість не може бути від\'ємною.',
        ];
    }
}
