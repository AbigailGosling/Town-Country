<x-app-layout :hideLink="true">
    @php
        $currencyColumns = ['Insured Credit', 'Outstanding Balance', 'Difference'];
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
            Credit Exposure Report
        </h2>
    </x-slot>

    <div class="pl-4 pr-4 pt-4">
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-sm text-gray-500"> Customers over insured credit</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $totals['over_limit_count'] }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total Insured Credit</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $formatCurrency((float) $totals['insured_credit']) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total Outstanding</div>
                    <div class="text-lg font-semibold text-gray-900">{{ $formatCurrency((float) $totals['outstanding']) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total Difference</div>
                    <div class="text-lg font-semibold text-red-600">
                        {{ $formatCurrency((float) $totals['difference']) }}
                    </div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between gap-4 flex-wrap">
                <div class="text-sm text-gray-600">
                    Customers : <span class="font-semibold text-gray-900">{{ $totals['customer_count'] }}</span>
                </div>
                <x-form-button id="export" title="Loading..." background="green" iconClass="fa-solid fa-file-spreadsheet" :disable="true" :fixed="true"></x-form-button>
            </div>
        </div>
    </div>

    @if ($data->count() > 0)
        <x-data-table>
            <x-slot:headers>
                @foreach ($data[0] as $key => $value)
                    @if (strlen($key) > 1)
                        <x-data-table-header>{{ $key }}</x-data-table-header>
                    @endif
                @endforeach
            </x-slot:headers>
            <slot>
                @foreach ($data as $item)
                    <tr>
                        @foreach ($item as $key => $value)
                            @if (strlen($key) > 1)
                                <x-data-table-column>
                                    @if (in_array($key, $currencyColumns, true))
                                        @if ($key === 'Difference')
                                            <span class="text-red-600">
                                                {{ $formatCurrency((float) $value) }}
                                            </span>
                                        @else
                                            {{ $formatCurrency((float) $value) }}
                                        @endif
                                    @else
                                        {{ $value }}
                                    @endif
                                </x-data-table-column>
                            @endif
                        @endforeach
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
