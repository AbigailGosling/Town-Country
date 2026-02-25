<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Outgoing Pallets (Next 3 Days)') }}
        </h2>
    </x-slot>

    <div class="pl-4">
        <p class="text-sm text-gray-600">
            {{ $startDate->format('d/m/Y') }} to {{ $endDate->format('d/m/Y') }}
        </p>
    </div>

    @if($deliveryGroups->isEmpty())
        <div class="pl-4 pt-4">
            <div class="text-sm text-gray-600">No outgoing pallets in the next 3 days.</div>
        </div>
    @else
        <x-data-table>
            <x-slot:headers>
                <x-data-table-header>Customer</x-data-table-header>
                <x-data-table-header>Address</x-data-table-header>
                <x-data-table-header>Expected Picks</x-data-table-header>
                <x-data-table-header>Details</x-data-table-header>
            </x-slot:headers>
            <slot>
                @foreach($deliveryGroups as $group)
                    <tr>
                        <x-data-table-column>{{ $group['customer_name'] }}</x-data-table-column>
                        <x-data-table-column>
                            @foreach($group['address_lines'] as $line)
                                <div>{{ $line }}</div>
                            @endforeach
                        </x-data-table-column>
                        <x-data-table-column>
                            <ul class="list-disc pl-4">
                                @foreach($group['deliveries_by_date'] as $date => $count)
                                    <li>
                                        {{ $date }}: <strong>{{ $count }}</strong> pick{{ $count != 1 ? 's' : '' }}
                                    </li>
                                @endforeach
                            </ul>
                        </x-data-table-column>
                        <x-data-table-column>
                            <a href="{{ route('outgoing-pallets.details', ['customer_id' => $group['customer_id'], 'address_id' => $group['address_id']]) }}">
                                <button class="rounded bg-green-500 hover:bg-green-700 w-6 h-6" href=""><i class="fas fa-edit text-green-100"></i></button>
                            </a>
                        </x-data-table-column>
                    </tr>
                @endforeach
            </slot>
        </x-data-table>
    @endif
</x-app-layout>
