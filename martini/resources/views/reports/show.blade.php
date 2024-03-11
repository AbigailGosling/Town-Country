<?php
$col = "sky-200";
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $report->name}}
        </h2>
    </x-slot>
    <div style="width:100%">
    <x-data-table :headerColour="$col">
        <x-slot:headers>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column)}}</x-data-table-header>
            @endforeach
        </x-slot:headers>
        <slot>
            @foreach($debits as $row)
            <tr>
                @foreach($columns as $column)
                <td align="center">{{App\Helpers\ReportHelper::finaliseCell($column,$row)}}</td>
                @endforeach
            </tr>
            @endforeach
        </slot>
        <x-slot:footers>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$debits)}}</x-data-table-header>
            @endforeach
        </x-slot:footers>
    </x-data-table>
    <x-data-table>
        <x-slot:headers>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column)}}</x-data-table-header>
            @endforeach
        </x-slot:headers>
        <slot>
            @foreach($credits as $row)
            <tr>
                @foreach($columns as $column)
                <td align="center">{{App\Helpers\ReportHelper::finaliseCell($column,$row)}}</td>
                @endforeach
            </tr>
            @endforeach
        </slot>
        <x-slot:footers>
            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$credits)}}</x-data-table-header>
            @endforeach
        </x-slot:footers>
    </x-data-table>
    </div>
</x-app-layout>
