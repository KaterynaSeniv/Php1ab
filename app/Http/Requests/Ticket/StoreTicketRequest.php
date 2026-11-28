<?php

declare(strict_types=1);

namespace App\Http\Requests\Ticket;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Ticket::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'train_route_id' => ['required', 'integer', 'exists:train_routes,id'],
            'passenger_full_name' => ['required', 'string', 'min:3', 'max:255'],
            'car_number' => ['required', 'integer', 'min:1', 'max:25'],
            'seat_number' => ['required', 'integer', 'min:1', 'max:54'],
            'services' => ['nullable', 'array'],
            'services.*' => ['integer', 'exists:services,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'train_route_id.required' => 'Оберіть рейс поїзда.',
            'train_route_id.exists' => 'Обраний рейс не знайдено.',
            'passenger_full_name.required' => 'Поле "ПІБ пасажира" обов\'язкове.',
            'passenger_full_name.min' => 'ПІБ має містити мінімум 3 символи.',
            'car_number.required' => 'Оберіть місце на схемі.',
            'seat_number.required' => 'Оберіть місце на схемі.',
        ];
    }
}
