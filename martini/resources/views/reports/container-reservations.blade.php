<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Container Reservation Summary') }}
            @if ($container)
                - {{ $container->internal_number }}
            @endif
        </h2>
    </x-slot>

    <div class="py-6 px-6">
        <form method="POST" action="{{ route('containers.reservations-report') }}" class="mb-6 rounded-lg bg-white p-4 shadow-sm">
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <x-input-label for="internal_number" :value="__('Internal Number')" />
                    <x-text-input id="internal_number" class="block mt-1 w-full" type="text" name="internal_number" :value="old('internal_number', $internal_number ?? '')" placeholder="Enter internal container number" required />
                    <x-input-error :messages="$errors->get('internal_number')" class="mt-2" />
                </div>
                <div>
                    <x-form-button title="Look Up Container" background="green" iconClass="fa-magnifying-glass" :submit="true" />
                </div>
            </div>
        </form>

        @if ($error)
            <div class="mb-6 rounded border border-red-200 bg-red-50 p-4 text-red-700">
                {{ $error }}
            </div>
        @endif

        @if ($container)
            <div class="mb-6 rounded-lg bg-white p-4 shadow-sm">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">Internal Number</div>
                        <div class="mt-1 text-lg font-semibold">{{ $container->internal_number }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">Origin Port</div>
                        <div class="mt-1 text-lg font-semibold">{{ $container->origin_port }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">ETA</div>
                        <div class="mt-1 text-lg font-semibold">{{ $container->eta ? $container->eta->format('d/m/Y') : 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500">Reservations</div>
                        <div class="mt-1 text-lg font-semibold">{{ $totalReservationCount }}</div>
                    </div>
                </div>
            </div>

            <div class="mb-6 rounded-lg bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-lg font-semibold text-slate-800">Container Product Summary</h3>
                <x-data-table>
                    <x-slot:headers>
                        <x-data-table-header>Product</x-data-table-header>
                        <x-data-table-header>Brand</x-data-table-header>
                        <x-data-table-header>Cases</x-data-table-header>
                        <x-data-table-header>Est KG</x-data-table-header>
                        <x-data-table-header>RRP</x-data-table-header>
                        <x-data-table-header>Cost</x-data-table-header>
                    </x-slot:headers>
                    <slot>
                        @forelse ($containerBreakdown as $item)
                            <tr>
                                <x-data-table-column>{{ $item['product'] }}</x-data-table-column>
                                <x-data-table-column>{{ $item['brand'] }}</x-data-table-column>
                                <x-data-table-column>{{ number_format($item['cases']) }}</x-data-table-column>
                                <x-data-table-column>{{ number_format($item['kg'], 3) }}</x-data-table-column>
                                <x-data-table-column>£{{ number_format((float) $item['rrp'], 2) }}</x-data-table-column>
                                <x-data-table-column>£{{ number_format((float) $item['cost'], 2) }}</x-data-table-column>
                            </tr>
                        @empty
                            <tr>
                                <x-data-table-column colspan="6">No products listed on this container.</x-data-table-column>
                            </tr>
                        @endforelse
                    </slot>
                </x-data-table>
            </div>

            <div class="rounded-lg bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-lg font-semibold text-slate-800">Reservations Against This Container</h3>
                <x-data-table>
                    <x-slot:headers>
                        <x-data-table-header>Reservation ID</x-data-table-header>
                        <x-data-table-header>Customer</x-data-table-header>
                        <x-data-table-header>ETA</x-data-table-header>
                        <x-data-table-header>Cases</x-data-table-header>
                        <x-data-table-header>Items</x-data-table-header>
                    </x-slot:headers>
                    <slot>
                        @forelse ($reservations as $reservation)
                            <tr>
                                <x-data-table-column>{{ $reservation['reservation_id'] }}</x-data-table-column>
                                <x-data-table-column>{{ $reservation['customer'] }}</x-data-table-column>
                                <x-data-table-column>{{ $reservation['eta'] }}</x-data-table-column>
                                <x-data-table-column>{{ number_format((int) $reservation['total_cases']) }}</x-data-table-column>
                                <x-data-table-column>
                                    <ul class="list-disc pl-4">
                                        @foreach ($reservation['items'] as $item)
                                            <li>{{ $item['product'] }} ({{ $item['brand'] }}) - {{ $item['cases'] }} cases @ £{{ number_format((float) $item['price'], 2) }}</li>
                                        @endforeach
                                    </ul>
                                </x-data-table-column>
                            </tr>
                        @empty
                            <tr>
                                <x-data-table-column colspan="5">No reservations have been placed against this container yet.</x-data-table-column>
                            </tr>
                        @endforelse
                    </slot>
                </x-data-table>
            </div>
        @endif
    </div>
</x-app-layout>
