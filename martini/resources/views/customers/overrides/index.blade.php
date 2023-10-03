<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer Overrides') }}
        </h2>
    </x-slot>
    <form method="get" action="{{route('overrides.search')}}">
            <x-search search_term="{{$search_term ? $search_term : ''}}">
            </x-search>
    </form>
    </div style="width:50%">
    

            <x-data-table>
                <x-slot:headers>
                    <x-data-table-header>Business Name</x-data-table-header>
                    <x-data-table-header></x-data-table-header>
                </x-slot:headers>
                <slot>
                    @foreach($customers as $customer)
                        <tr>
                            <x-data-table-column :show-on-mobile="false">{{$customer->businessname}}</x-data-table-column>
                            <td class="border-b dark:border-slate-600 p-2 pr-8">
                                <div class="grid-cols-2">
                                    <div class="grid grid-cols-1">
                                        <x-table-action-button route="overrides.edit" :id="$customer->id"></x-table-action-button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </slot>
            </x-data-table>
        <br>
            {{ $customers->links() }}
</x-app-layout>
