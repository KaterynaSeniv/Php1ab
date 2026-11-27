<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::withCount('tickets')
            ->orderBy('name')
            ->paginate(10);

        return view('services.index', compact('services'));
    }

    public function create(): View
    {
        $this->authorize('create', Service::class);

        return view('services.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Service::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:100', 'unique:services,name'],
            'price' => ['required', 'numeric', 'min:0'],
        ], [
            'name.required' => 'Поле "Назва послуги" обов\'язкове.',
            'name.min' => 'Назва має містити мінімум 2 символи.',
            'name.unique' => 'Послуга з такою назвою вже існує.',
            'price.required' => 'Поле "Вартість" обов\'язкове.',
            'price.numeric' => 'Вартість має бути числом.',
            'price.min' => 'Вартість не може бути від\'ємною.',
        ]);

        Service::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) ?: 'service-'.time(),
            'price' => (float) $validated['price'],
        ]);

        return redirect()
            ->route('services.index')
            ->with('success', 'Додаткову послугу успішно створено!');
    }

    public function show(Service $service): View
    {
        $service->load('tickets.trainRoute');

        return view('services.show', compact('service'));
    }

    public function edit(Service $service): View
    {
        $this->authorize('update', $service);

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('services', 'name')->ignore($service->id),
            ],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $service->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) ?: $service->slug,
            'price' => (float) $validated['price'],
        ]);

        return redirect()
            ->route('services.index')
            ->with('success', 'Послугу «'.$service->name.'» оновлено!');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Послугу видалено зі списку.');
    }
}
