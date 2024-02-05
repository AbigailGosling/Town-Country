<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Active Holiday Cover') }}
        </h2>
    </x-slot>
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div><x-form-button title="{{ 'Create New' }}" iconClass="fa-pencil" background="green"
                   route="holidays.create">
    </x-form-button></div>
    <div></div>
    <div></div>
    </div>
    

            <x-data-table>
                <x-slot:headers>
                    <x-data-table-header>Absentee</x-data-table-header>
                    <x-data-table-header>Covered by</x-data-table-header>
                    <x-data-table-header></x-data-table-header>
                </x-slot:headers>
                <slot>
                    @foreach($hcs as $hc)
                        <tr>
                            <x-data-table-column :show-on-mobile="true">{{$hc->absentUser()->name}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="true">{{$hc->coverUser()->name}}</x-data-table-column>
                            <td class="border-b dark:border-slate-600 p-1 pr-1">
                                <div class="grid-cols-1">
                                    <x-table-action-button route="holidays.edit" :id="$hc->id"></x-table-action-button>
                                </div> 
                            </td>
                            <td class="border-b dark:border-slate-600 p-1 pr-1">
                                <div class="grid-cols-1">   
                                    <x-table-action-button route="holidays.delete" :id="$hc->id" type="delete"></x-table-action-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </slot>
            </x-data-table>
        <br>
            {{ $hcs->links() }}
</x-app-layout>
