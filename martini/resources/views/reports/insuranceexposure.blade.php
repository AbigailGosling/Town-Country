<x-app-layout :hideLink="true">
    @php
    if (!isset($detailedView)) $detailedView = false;
        $currencyColumns = ['Total'];
        $formatCurrency = static function (float $value): string {
            $currencySymbol = '£';
            if ($value < 0) {
                $currencySymbol = '-£';
            }
            return $currencySymbol . number_format(abs($value), 2, '.', ',');
        };
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Insurance Exposure Report
        </h2>
    </x-slot>

    <div class="pl-4 pr-4 pt-4">
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Customers</div>
                    <div class="text-lg font-semibold text-gray-900">{{ count($data) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $formatCurrency($data->sum('total_outstanding')) }}</div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between gap-4 flex-wrap">
                <div class="text-sm text-gray-600">--</div>
                @if (!$detailedView)
                    <form method="GET" action="{{route('insuranceexposurereport.index')}}">
                        <input type="hidden" id="detailed-view" name="detailed-view" value="1">
                        <x-form-button title="Show Detailed View" background="green" iconClass="" :submit="true"></x-form-button>
                    </form>
                @else
                    <form method="GET" action="{{route('insuranceexposurereport.index')}}">
                        <input type="hidden" id="detailed-view" name="detailed-view" value="0">
                        <x-form-button title="Show Simplifed View" background="green" iconClass="" :submit="true"></x-form-button>
                    </form>
                @endif
                <x-form-button id="export" title="Loading..." background="green" iconClass="fa-solid fa-file-spreadsheet" :disable="true" :fixed="true"></x-form-button>
            </div>
        </div>
    </div>

    @if (count($data) > 0)
        <x-data-table>
            <x-slot:headers>
                <x-data-table-header :width="'6%'">Name</x-data-table-header>
                <x-data-table-header :width="'10%'">Contact</x-data-table-header>
                @if ($detailedView)
                <x-data-table-header :width="'16%'">Approaching Terms</x-data-table-header>
                <x-data-table-header :width="'5%'">Subtotal</x-data-table-header>
                @else
                <x-data-table-header :width="'16%'"> Under 28 Subtotal</x-data-table-header>
                @endif
                @if ($detailedView)
                <x-data-table-header :width="'16%'">Over Terms</x-data-table-header>
                <x-data-table-header :width="'5%'">Subtotal</x-data-table-header>
                @else
                <x-data-table-header :width="'16%'">28 - 35 Subtotal</x-data-table-header>
                @endif
                @if ($detailedView)
                <x-data-table-header :width="'16%'">Over Grace</x-data-table-header>
                <x-data-table-header :width="'5%'">Subtotal</x-data-table-header>
                @else
                <x-data-table-header :width="'16%'">Over 35 Subtotal</x-data-table-header>
                @endif
                <x-data-table-header :width="'5%'">Total</x-data-table-header>
            </x-slot:headers>
            <slot>
                @foreach ($data as $item)
                    <tr>
                        <x-data-table-column>{{$item->customer->businessname}}</x-data-table-column>
                        <x-data-table-column>{{$item->customer->accounts_contact}}<br/>{{$item->customer->tel_number}}</x-data-table-column>
                        @if ($detailedView)
                        <x-data-table-column>
                            <ul class="list-disc pl-5">
                                @foreach ($item->at as $invoice)
                                    <li>{{ $invoice->id }} - {{ $invoice->date }} - {{ $formatCurrency($invoice->outstanding) }}</li>
                                @endforeach
                            </ul>
                        </x-data-table-column>
                        @endif
                        <x-data-table-column :align="'center'">{{ ($item->at_total_outstanding > 0) ? $formatCurrency($item->at_total_outstanding) : '' }}</x-data-table-column>
                        @if ($detailedView)
                        <x-data-table-column>
                            <ul class="list-disc pl-5">
                                @foreach ($item->ot as $invoice)
                                    <li class="text-amber-600">{{ $invoice->id }} - {{ $invoice->date }} - {{ $formatCurrency($invoice->outstanding) }}</li>
                                @endforeach
                            </ul>
                        </x-data-table-column>
                        @endif
                        <x-data-table-column :align="'center'"><span class="text-amber-600">{{ ($item->ot_total_outstanding > 0) ? $formatCurrency($item->ot_total_outstanding) : '' }}</span></x-data-table-column>
                        @if ($detailedView)
                        <x-data-table-column>
                            <ul class="list-disc pl-5">
                                @foreach ($item->gt as $invoice)
                                    <li class="text-red-600">{{ $invoice->id }} - {{ $invoice->date }} - {{ $formatCurrency($invoice->outstanding) }}</li>
                                @endforeach
                            </ul>
                        </x-data-table-column>
                        @endif
                        <x-data-table-column :align="'center'"><span class="text-red-600">{{ ($item->gt_total_outstanding > 0) ? $formatCurrency($item->gt_total_outstanding) : '' }}</span></x-data-table-column>
                        <x-data-table-column :align="'center'"><span>{{ ($item->total_outstanding > 0) ? $formatCurrency($item->total_outstanding) : '' }}</span></x-data-table-column>
                    </tr>
                @endforeach
            </slot>
        </x-data-table>
    @else
        <x-data-table>
            <x-slot:headers><x-data-table-header> </x-data-table-header></x-slot:headers>
            <slot><tr><td><b>NO ITEMS TO SHOW</b></td></tr></slot>
        </x-data-table>
    @endif
    <br>
</x-app-layout>

<?php
use Carbon\Carbon;
?>
@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script>
    setTimeout(readyEvent, 10);

    function readyEvent()
    {
        $("#export").css("pointer-events", "auto");
        $("#export-text").text(" Export ");
    }

    function ExportData()
    {
        filename = 'insured_credit_report_{{ Carbon::now()->format('Y_m_d_His') }}.xlsx';
        wb = XLSX.utils.book_new();
        data = {!! json_encode($data) !!};
        ws = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "1");
        XLSX.writeFile(wb, filename);
    }
</script>
