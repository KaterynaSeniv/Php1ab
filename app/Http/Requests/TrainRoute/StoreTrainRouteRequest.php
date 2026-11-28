<?php

declare(strict_types=1);

namespace App\Http\Requests\TrainRoute;

use App\Models\TrainRoute;
use Illuminate\Foundation\Http\FormRequest;

class StoreTrainRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TrainRoute::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'train_number' => ['required', 'string', 'unique:train_routes,train_number'],
            'departure_station' => ['required', 'string', 'min:2', 'max:100'],
            'arrival_station' => ['required', 'string', 'min:2', 'max:100'],
            'departure_time' => ['required', 'date', 'after:now'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'car_count' => ['required', 'integer', 'min:1', 'max:25'],
            'seats_per_car' => ['required', 'integer', 'min:1', 'max:54'],
            'car_type' => ['required', 'in:coupe,platskart'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'train_number.required' => 'Поле "Номер поїзда" обов\'язкове для заповнення.',
            'train_number.unique' => 'Поїзд з таким номером вже існує.',
            'departure_station.required' => 'Поле "Станція відправлення" обов\'язкове.',
            'departure_station.min' => 'Назва станції має містити мінімум 2 символи.',
            'arrival_station.required' => 'Поле "Станція прибуття" обов\'язкове.',
            'arrival_station.min' => 'Назва станції має містити мінімум 2 символи.',
            'departure_time.required' => 'Поле "Час відправлення" обов\'язкове.',
            'departure_time.date' => 'Некоректна дата та час відправлення.',
            'departure_time.after' => 'Час відправлення має бути в майбутньому.',
            'base_price.required' => 'Поле "Базова вартість" обов\'язкове.',
            'base_price.numeric' => 'Базова вартість має бути числом.',
            'base_price.min' => 'Базова вартість не може бути від\'ємною.',
            'car_count.required' => 'Поле "Кількість вагонів" обов\'язкове.',
            'car_count.min' => 'Кількість вагонів має бути від 1 до 25.',
            'car_count.max' => 'Кількість вагонів має бути від 1 до 25.',
            'seats_per_car.required' => 'Поле "Місць у вагоні" обов\'язкове.',
            'seats_per_car.min' => 'Кількість місць має бути від 1 до 54.',
            'seats_per_car.max' => 'Кількість місць має бути від 1 до 54.',
            'car_type.required' => 'Оберіть тип вагонів.',
            'car_type.in' => 'Тип вагонів має бути або купе, або плацкарт.',
        ];
    }
}
