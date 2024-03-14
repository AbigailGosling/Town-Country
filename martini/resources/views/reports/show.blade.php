<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $report->name}}
        </h2>
    </x-slot>
    <div style="width:100%">
    <div class="bg-gray-200 shadow-sm sm:rounded-lg ml-6 mr-6">
    <table class="border-collapse table-auto w-full text-sm mt-4">
        <thead class="bg-sky-200" style="position: sticky; top: 0;"><tr>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column)}}</x-data-table-header>
            @endforeach
        </tr></thead>
        <tbody class="bg-white">
            @foreach($debits as $row)
            <tr>
                @foreach($columns as $column)
                <td align="center">{{App\Helpers\ReportHelper::finaliseCell($column,$row)}}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-sky-100" style="position: sticky; bottom: 0;"><tr>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$debits)}}</x-data-table-header>
            @endforeach
        </tr></tfoot>
    </table>
    </div>
    <div class="bg-gray-200 shadow-sm sm:rounded-lg ml-6 mr-6">
    <table class="border-collapse table-auto w-full text-sm mt-4">
        <thead class="bg-orange-200" style="position: sticky; top: 0;"><tr>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column)}}</x-data-table-header>
            @endforeach
        </tr></thead>
        <tbody class="bg-white">
            @foreach($credits as $row)
            <tr>
                @foreach($columns as $column)
                <td align="center">{{App\Helpers\ReportHelper::finaliseCell($column,$row)}}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-orange-100" style="position: sticky; bottom: 0;"><tr>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$credits)}}</x-data-table-header>
            @endforeach
        </tr></tfoot>
    </table>
    </div>
    </div>
</x-app-layout>
