<?php
$export = [];
?>
    <x-app-layout :hideLink="true">
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div><x-form-button id="export" title="Loading..." background="green" iconClass="fa-file-spreadsheet" :disable="true" :fixed="true">
        </x-form-button></div>
    <div></div>
    <div></div>
    </div>
            <x-data-table>
                <x-slot:headers>
                    @foreach ($data[0] as $key=>$value)
                    @if ($key != "user" && $key != "site")
                        <x-data-table-header>{{$key}}</x-data-table-header>
                    @endif
                    @endforeach
                </x-slot:headers>
                <slot>
                    @foreach($data as $item)
                    <tr>
                    <?php $row = []; ?>
                    @foreach ($item as $key=>$value)
                    @if ($key != "user" && $key != "site")
                        <x-data-table-column>{{$value}}</x-data-table-column>
                        <?php $row[$key] = $value; ?>
                        @endif
                    @endforeach
                    <?php $export[] = $row; ?>
                    </tr>
                    @endforeach
                </slot>
            </x-data-table>
        <br>
</x-app-layout>
<?php
use Carbon\Carbon;
?>
@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script>
    setTimeout(readyEvent, 10);
    function readyEvent() {
        $("#export").css("pointer-events","auto");
        $("#export-text").text(" Export ");
     }
    function ExportData()
    {
        filename='customer_salesperson_{{Carbon::now()}}.xlsx';
        wb = XLSX.utils.book_new();
        data={!!json_encode($export)!!};
        ws = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "1");
        XLSX.writeFile(wb,filename);
     }
</script>
