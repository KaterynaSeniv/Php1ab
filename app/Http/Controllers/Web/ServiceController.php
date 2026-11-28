<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use App\Services\ServiceCatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceCatalogService $serviceCatalog) {}

    public function index(): View
    {
        $services = $this->serviceCatalog->getPaginatedList();

        return view('services.index', compact('services'));
    }

    public function create(): View
    {
        $this->authorize('create', Service::class);

        return view('services.create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $this->serviceCatalog->createService($request->validated());

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

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $this->serviceCatalog->updateService($service, $request->validated());

        return redirect()
            ->route('services.index')
            ->with('success', 'Послугу «'.$service->name.'» оновлено!');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);
        $this->serviceCatalog->deleteService($service);

        return redirect()
            ->route('services.index')
            ->with('success', 'Послугу видалено зі списку.');
    }
}
