<?php
 $extras = (request()->has("history"))?"?history=1":"";
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if (!request()->has("history"))
            {{ __('Supplier Return Statements') }}
            @else
            {{ __('Supplier Return History') }}
            @endif
        </h2>
    </x-slot>
    <div></div>
    <div></div>
    <form method="get" action="{{route('supplierreturnstatements.index')}}">
            <x-search search_term="{{$search_term ? $search_term : ''}}">
            </x-search>
    </form>
    </div>


            <x-data-table>
                <x-slot:headers>
                    <x-data-table-header :show-on-mobile="false">Supplier</x-data-table-header>
                    @if (!request()->has("history"))
                    <x-data-table-header><b>Current Outstanding</b></x-data-table-header>
                    <x-data-table-header :show-on-mobile="false">Paid</x-data-table-header>
                    @endif
                    <x-data-table-header>Notes</x-data-table-header>
                    <x-data-table-header>View</x-data-table-header>
                </x-slot:headers>
                <slot>
                    @foreach($items as $item)
                    @if($item->outstanding>0.01 || $item->outstanding<-0.01 || request()->has("history"))
                        <tr>
                            <x-data-table-column :show-on-mobile="false">{{$item->supplier->name}}</x-data-table-column>
                            @if (!request()->has("history"))
                            <x-data-table-column :show-on-mobile="true"><b>£{{number_format($item->outstanding,2)}}</b></x-data-table-column>
                            <x-data-table-column :show-on-mobile="false">£{{number_format($item->paid,2)}}</x-data-table-column>
                            @endif
                            @if (strlen($item->supplier->return_notes)>15)
                            <x-data-table-column :show-on-mobile="false">{{substr($item->supplier->return_notes,0,12)."..."}}</x-data-table-column>
                            @else
                            <x-data-table-column :show-on-mobile="false">{{$item->supplier->return_notes}}</x-data-table-column>
                            @endif
                            <td class="border-b dark:border-slate-600 p-2 pr-8">
                                <div class="grid-cols-2">
                                    <div class="grid grid-cols-1">
                                        <x-table-action-button route="supplierreturnstatements.show" :id="$item->supplier->id" :extras="$extras"></x-table-action-button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                    @endforeach
                </slot>
                <x-slot:footers>
                    <x-data-table-column :show-on-mobile="false">Total:</x-data-table-column>
                    @if (!request()->has("history"))
                    <x-data-table-column :show-on-mobile="true"><b>£{{number_format($summary->outstanding,2)}}</b></x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">£{{number_format($summary->paid,2)}}</x-data-table-column>
                    @endif
                    <x-data-table-column></x-data-table-column>
                    <x-data-table-column></x-data-table-column>
                </x-slot:footers>
            </x-data-table>
        <br>
</x-app-layout>
