<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $report->name}}
        </h2>
    </x-slot>
    <x-data-table>
        <x-slot:headers>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column)}}</x-data-table-header>
            @endforeach
        </x-slot:headers>
        <slot>
            @foreach($data as $row)
            <tr>
                @foreach($columns as $column)
                <td align="center">{{App\Helpers\ReportHelper::finaliseCell($column,$row)}}</td>
                @endforeach
            </tr>
            @endforeach
        </slot>
        <x-slot:footers>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$data)}}</x-data-table-header>
            @endforeach
        </x-slot:footers>
    </x-data-table>
</x-app-layout>
