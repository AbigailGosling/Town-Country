<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pending Internal Pallet Movements') }}
        </h2>
    </x-slot>

    @if($movements->isEmpty())
        <div class="pl-4 pt-4">
            <div class="text-sm text-gray-600">No unprocessed internal pallet movements found.</div>
        </div>
    @else
        <x-data-table>
            <x-slot:headers>
                <x-data-table-header>Created</x-data-table-header>
                <x-data-table-header>Pallet ID</x-data-table-header>
                <x-data-table-header>Products</x-data-table-header>
                <x-data-table-header>Movement</x-data-table-header>
                <x-data-table-header>Initiated By</x-data-table-header>
                <x-data-table-header></x-data-table-header> <!-- Accept Button -->
                <x-data-table-header></x-data-table-header> <!-- Reject Button -->
            </x-slot:headers>
            <slot>
                @foreach($movements as $movement)
                    <tr>
                        <x-data-table-column>{{ $movement->created_at?->format('d/m/Y H:i') ?? '-' }}</x-data-table-column>
                        <x-data-table-column>{{ $movement->pallet_id }}</x-data-table-column>
                        <x-data-table-column :align="'center'">
                            @php
                                $products = $movement->pallet?->products ?? collect();
                            @endphp
                            @if($products->isEmpty())
                                <span class="text-gray-400">No products</span>
                            @else
                                <ul class="list-disc pl-4">
                                @foreach($products as $product)
                                    <li>
                                        {{ $product->cut?->name ?? '-' }} /
                                        {{ $product->nationality?->name ?? '-' }} /
                                        {{ $product->brand?->name ?? '-' }}
                                        @php
                                        $expectedWeight = 0;
                                        $expectedCases = 0;
                                        foreach ($product->weights as $weight) {
                                            if ($weight->status_id === 0) {
                                                $expectedWeight += $weight->getNetWeight() ?? 0;
                                                $expectedCases++;
                                            }
                                        }
                                        @endphp
                                        <br/><strong>Expected:</strong> {{ number_format($expectedWeight, 2) }} kg, {{ $expectedCases }} cases
                                    </li>
                                @endforeach
                                </ul>
                            @endif
                        </x-data-table-column>
                        <x-data-table-column :align="'center'">{{ $movement->fromLocation?->site?->abbreviation ?? '-' }} {{ $movement->fromLocation?->name ?? '-' }} -> {{ $movement->toLocation?->site?->abbreviation ?? '-' }} {{ $movement->toLocation?->name ?? '-' }}</x-data-table-column>
                        <x-data-table-column :align="'right'">{{ $movement->initiatedBy?->name ?? '-' }}</x-data-table-column>
                        <x-data-table-column>
                            <form method="POST" action="{{ route('internal-pallet-movements.accept', $movement) }}">
                                @csrf
                                <button type="submit" class="rounded bg-green-500 hover:bg-green-700 w-8 h-8" title="Accept">
                                    <i class="fas fa-check text-green-100"></i>
                                </button>
                            </form>
                        </x-data-table-column>
                        <x-data-table-column :align="'right'">
                            <form method="POST" action="{{ route('internal-pallet-movements.reject', $movement) }}">
                                @csrf
                                <button type="submit" class="rounded bg-red-500 hover:bg-red-700 w-8 h-8" title="Reject">
                                    <i class="fas fa-times text-red-100"></i>
                                </button>
                            </form>
                        </x-data-table-column>
                    </tr>
                @endforeach
            </slot>
        </x-data-table>
    @endif
</x-app-layout>
