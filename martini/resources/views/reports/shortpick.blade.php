<x-app-layout :hideLink="true">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Short Pick Report
        </h2>
    </x-slot>
    <div align="left" class="pl-4 pr-4 pt-4">
        <form align="left" method="GET" action="{{ route('shortpick.index') }}">
            <x-form>
                <x-form-section :columns="1">
                    <div>
                        <x-input-label for="picksheet_id" :value="__('Picksheet ID')"/>
                        <x-text-input id="picksheet_id" class="block mt-1 w-full" type="number" name="picksheet_id" :value="old('picksheet_id', $filters['picksheet_id'])"/>
                        <x-input-error :messages="$errors->get('picksheet_id')" class="mt-2"/>
                    </div>
                    <div>
                        <x-input-label for="start_date" :value="__('Start Date')"/>
                        <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $filters['start_date'])"/>
                        <x-input-error :messages="$errors->get('start_date')" class="mt-2"/>
                    </div>
                    <div>
                        <x-input-label for="end_date" :value="__('End Date')"/>
                        <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $filters['end_date'])"/>
                        <x-input-error :messages="$errors->get('end_date')" class="mt-2"/>
                    </div>
                </x-form-section>
                <x-slot name="buttons">
                    <x-form-button title="Search" background="green" iconClass="fa-circle-arrow-right" :submit="true"></x-form-button>
                    <x-form-button id="export" title="Loading..." background="green" iconClass="fa-solid fa-file-spreadsheet" :disable="true" :fixed="true"></x-form-button>
                </x-slot>
            </x-form>
        </form>
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
                                <x-data-table-column>{{ $value }}</x-data-table-column>
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
        filename = 'short_pick_report_{{ Carbon::now() }}.xlsx';
        wb = XLSX.utils.book_new();
        data = {!! json_encode($data) !!};
        ws = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "1");
        XLSX.writeFile(wb, filename);
    }
</script>
