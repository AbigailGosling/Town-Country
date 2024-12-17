<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Customer List') }}
        </h2>
    </x-slot>
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div>
            <x-form-button id="export" title="Loading..." background="green" iconClass="fa-file-spreadsheet" :disable="true"></x-form-button>
        </div>
        <div></div>
        <div></div>
    </div>
    <x-data-table>
        <x-slot:headers>
            <x-data-table-header :show-on-mobile="false">User ID</x-data-table-header>
            <x-data-table-header>User Name</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Customer ID</x-data-table-header>
            <x-data-table-header>Customer Name</x-data-table-header>
        </x-slot:headers>
        <slot>
            @foreach($list as $user)
                <tr>
                    <x-data-table-column :show-on-mobile="false">{{$user->user_id}}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="true">{{$user->user_name}}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">{{$user->customer_id}}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="true">{{$user->customer_name}}</x-data-table-column>

                </tr>
            @endforeach
        </slot>
    </x-data-table>
</x-app-layout>
@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script>
    setTimeout(readyEvent, 1);
     function readyEvent() {
        $("#export").css("pointer-events","auto");
        $("#export-text").text(" Export ");
     }
     function ExportData()
    {
        filename='usercustomer_{{time()}}.xlsx';
        wb = XLSX.utils.book_new();
        data={!!json_encode($list)!!};
        ws = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "report");
        XLSX.writeFile(wb,filename);
     }
</script>
