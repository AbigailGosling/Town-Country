<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    private static int $defaultPaginate = 25;

    protected function baseQuery()
    {
        return Vehicle::with(['site', 'vehicleType'])->orderBy('reg');
    }

    public function __construct()
    {
        View::composer('vehicles.edit', function ($view) {
            $view->with('vehicleTypes', VehicleType::orderBy('name')->get());
            $view->with('sites', Site::where('disabled', false)->orderBy('name')->get());
        });
    }

    public function index()
    {
        return view('vehicles.index', [
            'vehicles' => $this->baseQuery()->paginate($this::$defaultPaginate),
            'search_term' => '',
        ]);
    }

    public function search(Request $request)
    {
        $searchTerm = trim((string) $request->get('search', ''));

        $vehicles = Vehicle::with(['site', 'vehicleType'])
            ->where(function ($q) use ($searchTerm) {
                $q->where('reg', 'like', '%' . $searchTerm . '%')
                    ->orWhere('make', 'like', '%' . $searchTerm . '%')
                    ->orWhere('model', 'like', '%' . $searchTerm . '%')
                    ->orWhere('driver', 'like', '%' . $searchTerm . '%');
            })
            ->orderBy('reg')
            ->paginate($this::$defaultPaginate)
            ->appends(request()->query());

        return view('vehicles.index', [
            'vehicles' => $vehicles,
            'search_term' => $searchTerm,
        ]);
    }

    public function create()
    {
        return view('vehicles.edit', ['vehicle' => new Vehicle, 'isNew' => true]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reg' => ['required', 'string', 'max:50', Rule::unique('tandc_live.vehicle', 'reg')],
            'vehicle_type_id' => ['nullable', 'integer', 'exists:tandc_live.vehicle_type,id'],
            'make' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'grossWeight' => ['nullable', 'string', 'max:255'],
            'payload' => ['nullable', 'string', 'max:255'],
            'site_id' => ['nullable', 'integer', 'exists:tandc_live.site,id'],
            'driver' => ['nullable', 'string', 'max:255'],
            'max_pallet_rows' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        $validated['reg'] = trim((string) $validated['reg']);
        $validated['max_pallet_rows'] = isset($validated['max_pallet_rows'])
            ? (int) $validated['max_pallet_rows']
            : 5;

        $vehicle = Vehicle::create($validated);

        return redirect(route('vehicles.edit', ['vehicle' => $vehicle->id]))
            ->with(['message' => 'Vehicle created successfully']);
    }

    public function show(Vehicle $vehicle)
    {
        return view('vehicles.edit', ['vehicle' => $vehicle, 'isNew' => false]);
    }

    public function edit(Vehicle $vehicle)
    {
        return $this->show($vehicle);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'reg' => ['required', 'string', 'max:50', Rule::unique('tandc_live.vehicle', 'reg')->ignore($vehicle->id)],
            'vehicle_type_id' => ['nullable', 'integer', 'exists:tandc_live.vehicle_type,id'],
            'make' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'grossWeight' => ['nullable', 'string', 'max:255'],
            'payload' => ['nullable', 'string', 'max:255'],
            'site_id' => ['nullable', 'integer', 'exists:tandc_live.site,id'],
            'driver' => ['nullable', 'string', 'max:255'],
            'max_pallet_rows' => ['nullable', 'integer', 'min:1', 'max:40'],
        ]);

        $validated['reg'] = trim((string) $validated['reg']);
        $validated['max_pallet_rows'] = isset($validated['max_pallet_rows'])
            ? (int) $validated['max_pallet_rows']
            : 5;

        $vehicle->fill($validated);
        $vehicle->save();

        return redirect(route('vehicles.index'))
            ->with(['message' => 'Vehicle updated successfully']);
    }

    public function destroy(Vehicle $vehicle)
    {
    }
}
