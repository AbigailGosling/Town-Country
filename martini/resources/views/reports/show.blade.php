<?php
$debits2 = [];
$credits2= [];
$supdebits2 = [];
$supcredits2 = [];
if (!isset($dateType)) $dateType = "assembled";
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ $report->name}}
        </h2>
    </x-slot>
    <div align="left">
    <form align="left" method="POST" action="{{route('report.show',[$report->id])}}">
    {{ method_field('POST') }}
    @csrf
        <x-form>
            <x-form-section columns="3">
                <div>
                    <x-input-label for="start" :value="__('Start')"/>

                    <x-text-input value="{{$start->format('Y-m-d')}}" id="start" class="block mt-1 w-full" type="date" name="start" required></x-text-input>

                    <x-input-error :messages="$errors->get('start')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="end" :value="__('End')"/>

                    <x-text-input value="{{$end->format('Y-m-d')}}" id="end" class="block mt-1 w-full" type="date" name="end" required></x-text-input>

                    <x-input-error :messages="$errors->get('end')" class="mt-2"/>
                </div>
            </x-form-section>
            <x-form-section columns="3">
                <div>
                    <x-input-label for="dateType" :value="__('Date Type')"/>
                    <select class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full' id="dateType" name="dateType">
                        <option value="created" @if($dateType == "created") selected @endif>Date Created</option>
                        <option value="assembled" @if($dateType == "assembled") selected @endif>Date Assembled</option>
                        <option value="delivered" @if($dateType == "delivered") selected @endif>Date Delivered</option>
                    </select>
                    <x-input-error :messages="$errors->get('selector')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="selector" :value="__('Select View')"/>
                    <select class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full' id="selector" name="selector">
                        @foreach($reports as $item)
                            <option value="{{$item->id}}" @if($item->id == $report->id) selected @endif>{{$item->name}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('selector')" class="mt-2"/>
                </div>
            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
                <x-form-button title="Search" background="green" iconClass="fa-circle-arrow-right" :submit="true">
                </x-form-button>
                <x-form-button id="export" title="Loading..." background="green" iconClass="fa-file-spreadsheet" :disable="true">
                </x-form-button>
            </x-slot>
    </x-form>
    </form>
    </div>
    <div>
        <div style="width:100%" class="bg-gray-200 shadow-sm sm:rounded-lg ml-6 mr-6">
            <table class="table-fixed w-fit text-sm mt-4">
                <?php $columns =$report->getTables()[0]->getColumns(); ?>
                <thead class="bg-sky-200" style="position: sticky; top: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column,"debits")}}</x-data-table-header>
                    @endforeach
                </tr></thead>
                <tbody class="bg-white">
                    @foreach($debits as $row)
                    <?php $d = new stdClass(); ?>
                    <tr>
                        @foreach($columns as $column)
                        <?php $t = App\Helpers\ReportHelper::finaliseCell($column,$row,"debits");$col = $column->getLabel("debits");$d->$col = $t ?>
                        <td align="center">{{$t}}</td>
                        @endforeach
                    </tr>
                    <?php $debits2[] = $d; ?>
                    @endforeach
                </tbody>
                <tfoot class="bg-sky-100" style="position: sticky; bottom: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$debits,"debits")}}</x-data-table-header>
                    @endforeach
                </tr></tfoot>
            </table>
            <table class="table-fixed w-fit text-sm mt-4">
                <?php $columns =$report->getTables()[1]->getColumns(); ?>
                <thead class="bg-orange-200" style="position: sticky; top: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column,"credits")}}</x-data-table-header>
                    @endforeach
                </tr></thead>
                <tbody class="bg-white">
                    @foreach($credits as $row)
                    <?php $c = new stdClass(); ?>
                    <tr>
                        @foreach($columns as $column)
                        <?php $t = App\Helpers\ReportHelper::finaliseCell($column,$row,"credits");$col = $column->getLabel("credits");$c->$col = $t ?>
                        <td align="center">{{$t}}</td>
                        @endforeach
                    </tr>
                    <?php $credits2[] = $c; ?>
                    @endforeach
                </tbody>
                <tfoot class="bg-orange-100" style="position: sticky; bottom: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$credits,"credits")}}</x-data-table-header>
                    @endforeach
                </tr></tfoot>
            </table>
            @if (count($supdebits)>0)
            <table class="table-fixed w-fit text-sm mt-4">
                <?php $columns =$report->getTables()[2]->getColumns(); ?>
                <thead class="bg-sky-200" style="position: sticky; top: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column,"debits")}}</x-data-table-header>
                    @endforeach
                </tr></thead>
                <tbody class="bg-white">
                    @foreach($supdebits as $row)
                    <?php $d = new stdClass(); ?>
                    <tr>
                        @foreach($columns as $column)
                        <?php $t = App\Helpers\ReportHelper::finaliseCell($column,$row,"debits");$col = $column->getLabel("debits");$d->$col = $t ?>
                        <td align="center">{{$t}}</td>
                        @endforeach
                    </tr>
                    <?php $supdebits2[] = $d; ?>
                    @endforeach
                </tbody>
                <tfoot class="bg-sky-100" style="position: sticky; bottom: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$supdebits,"debits")}}</x-data-table-header>
                    @endforeach
                </tr></tfoot>
            </table>    
            @endif
            @if (count($supcredits)>0)
            <table class="table-fixed w-fit text-sm mt-4">
                <?php $columns =$report->getTables()[3]->getColumns(); ?>
                <thead class="bg-orange-200" style="position: sticky; top: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column,"credits")}}</x-data-table-header>
                    @endforeach
                </tr></thead>
                <tbody class="bg-white">
                    @foreach($supcredits as $row)
                    <?php $c = new stdClass(); ?>
                    <tr>
                        @foreach($columns as $column)
                        <?php $t = App\Helpers\ReportHelper::finaliseCell($column,$row,"credits");$col = $column->getLabel("credits");$c->$col = $t ?>
                        <td align="center">{{$t}}</td>
                        @endforeach
                    </tr>
                    <?php $supcredits2[] = $c; ?>
                    @endforeach
                </tbody>
                <tfoot class="bg-orange-100" style="position: sticky; bottom: 0;"><tr>
                    @foreach($columns as $column)
                    <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$supcredits,"credits")}}</x-data-table-header>
                    @endforeach
                </tr></tfoot>
            </table>    
            @endif
        </div>
    </div>
</x-app-layout>
@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script>
    function ExportData()
    {
        filename='{{str_replace(" ","_",strtolower($report->name))}}_{{time()}}_{{$start->format("Y-m-d")}}_{{$end->format("Y-m-d")}}.xlsx';
        data={!!json_encode($debits2)!!};
        var ws = XLSX.utils.json_to_sheet(data);
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Debits");
        data={!!json_encode($credits2)!!};
        ws = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "Credits");
        
        if ({!!count($supdebits2)!!} > 0)
        {
            data={!!json_encode($supdebits2)!!};
            ws = XLSX.utils.json_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, "Supplemental Debits");
        }
        if ({!!count($supcredits2)!!} > 0)
        {
            data={!!json_encode($supcredits2)!!};
            ws = XLSX.utils.json_to_sheet(data);
            XLSX.utils.book_append_sheet(wb, ws, "Supplemental Credits");
        }
        XLSX.writeFile(wb,filename);
     }
     function readyEvent() {
        $("#export").css("pointer-events","auto");
        $("#export-text").text(" Export ");
     }
     setTimeout(readyEvent, 1);
     
</script>