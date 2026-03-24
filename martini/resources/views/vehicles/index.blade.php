<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vehicles') }}
        </h2>
    </x-slot>
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div><x-form-button title="{{ 'Create Vehicle' }}" iconClass="fa-pencil" background="green"
                   route="vehicles.create">
    </x-form-button></div>
    <div></div>
    <div></div>
    <form method="get" action="{{route('vehicles.search')}}">
            <x-search search_term="{{$search_term ? $search_term : ''}}">
            </x-search>
    </form>
    </div>

            <x-data-table>
                <x-slot:headers>
                    <x-data-table-header :show-on-mobile="false">Registration</x-data-table-header>
                    <x-data-table-header :show-on-mobile="false">Type</x-data-table-header>
                    <x-data-table-header :show-on-mobile="false">Site</x-data-table-header>
                    <x-data-table-header :show-on-mobile="false">Driver</x-data-table-header>
                    <x-data-table-header :show-on-mobile="false">Max Rows</x-data-table-header>
                    <x-data-table-header></x-data-table-header>
                </x-slot:headers>
                <slot>
                    @foreach($vehicles as $vehicle)
                        <tr>
                            <x-data-table-column :show-on-mobile="false">{{$vehicle->reg}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="false">{{$vehicle->vehicleType->name ?? ''}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="false">{{$vehicle->site->name ?? ''}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="false">{{$vehicle->driver}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="false">{{$vehicle->max_pallet_rows ?? 5}}</x-data-table-column>
                            <td class="border-b dark:border-slate-600 p-2 pr-8">
                                <div class="grid-cols-2">
                                    <div class="grid grid-cols-1">
                                        <x-table-action-button route="vehicles.edit" :id="$vehicle->id"></x-table-action-button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </slot>
            </x-data-table>
        <br>
            {{ $vehicles->links() }}
</x-app-layout>
