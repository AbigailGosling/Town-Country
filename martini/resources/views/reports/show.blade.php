<?php
global $processed;
$processed = [];
if (!isset($dateType)) $dateType = "assembled";
?>
<head>
<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.3/css/dataTables.dataTables.css" />  
</head>
<x-app-layout :expandH="false">
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
    
</x-app-layout>
@foreach($report->getTables() as $index=>$table)
<div class="bg-gray-200 shadow-sm sm:rounded-lg mb-2">
    <table id="myTable{{$index}}" class="table-fixed text-sm">
        <?php
            $columns =$table->getColumns();
            $processed[$table->name] = [];
            $data = $dataRanges[$index];
            $tablenameSimplified = preg_replace("/\W|_/", '', strtolower($table->name));
            ?>
        @if ($index%2==0)
        <thead class="bg-sky-200" style="position: sticky; top: 0;"><tr>
        @else
        <thead class="bg-orange-200" style="position: sticky; top: 0;"><tr>
        @endif
        @foreach($columns as $column)
        <x-data-table-header>{{App\Helpers\ReportHelper::resolveHeader($column,$table->mode)}}</x-data-table-header>
        @endforeach
        </tr></thead>
        <tbody class="bg-white">
            @foreach($data as $row)
            <?php $d = new stdClass(); ?>
            <tr>
                @foreach($columns as $column)
                <?php
                $t = App\Helpers\ReportHelper::finaliseCell($column,$row,$table->mode);
                $col = $column->getLabel($table->mode);
                $d->$col = preg_replace("/[£,]/", '', $t);
                $columnNameSimplified = preg_replace("/\W|_/", '', strtolower($column->getLabel($table->mode)));
                $fieldName = $tablenameSimplified . '_' .$columnNameSimplified . '_' .$row['internal_id'];
                ?>
                @if(isset($column->metadata) && isset($column->metadata['isInput']) && $column->metadata['isInput'] == true)
                <!--<td style="width:100px" align="center"><input style="width:100%" type="number" step="0.01" pattern="^\d*(\.\d{0,2})?$" onpaste="changed(this)" oncut="changed(this)" onkeyup="changed(this)" id="{{$fieldName}}" name="{{$fieldName}}" og="{{$d->$col}}">{{$t}}</input></td>-->
                @else
                <!--<td style="width:100px" align="center" id="{{$fieldName}}" og="{{$d->$col}}">{{$t}}</td>-->
                @endif
                @endforeach
            </tr>
            <?php $processed[$table->name][] = $d;?>
            @endforeach
        </tbody>
        @if ($index%2==0)
        <tfoot class="bg-sky-100" style="position: sticky; bottom: 0;"><tr>
        @else
        <tfoot class="bg-orange-100" style="position: sticky; bottom: 0;"><tr>
        @endif

            @foreach($columns as $column)
            <x-data-table-header>{{App\Helpers\ReportHelper::resolveFooter($column,$processed[$table->name],$table->mode)}}</x-data-table-header>
            @endforeach
        </tr></tfoot>
    </table>
</div>
@endforeach
@stack('scripts')
<script src="https://cdn.datatables.net/2.1.3/js/dataTables.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script>
    $(document).ready( function () {
        @foreach($report->getTables() as $index=>$table)
        <?php $columns = $table->getColumns();?>
        new DataTable('#myTable{{$index}}', {
            "lengthMenu": [[1000,-1], [1000,"All"]],
            data: [ 
                
                @foreach($processed[$table->name] as $row)
                {
                    @foreach($row as $key=>$value)
                        "{{$key}}":"{{$value}}",
                    @endforeach
                },    
                @endforeach
            ],
            columns: [
                @foreach($columns as $colIndex=>$column)
                    { data: '{{$column->getLabel($table->mode)}}'},
                @endforeach
            ]
        });
        @endforeach
        setTimeout(readyEvent, 1);
    });
     function readyEvent() {
        $("#export").css("pointer-events","auto");
        $("#export-text").text(" Export ");
     } 
     function changed(e){
        var d = $(e).attr('id');
        d = d.split("_");
        var tablename = d[0];
        var rowid = d[2];
        var output = parseFloat($('#'+tablename+"_actualprofit_"+rowid).attr("og")) || 0;
        output -= parseFloat($('#'+tablename+"_lesstransport_"+rowid).val()) || 0;
        output -= parseFloat($('#'+tablename+"_lessoverriders_"+rowid).val()) || 0;
        output -= parseFloat($('#'+tablename+"_lesscredits_"+rowid).val()) || 0;
        output -= parseFloat($('#'+tablename+"_lessother_"+rowid).val()) || 0;
        $('#'+tablename+"_netprofit_"+rowid).html("£"+output);
     }
     function cleanData(data){
        for (row of data) {
            for (property in row) {
                if (isNumeric(row[property]))
                {
                    row[property] = parseFloat(row[property]);
                }
            }
        }
        return data;
     }
     function isNumeric(str) {
        if (typeof str != "string") return false // we only process strings!
        return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
                !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
    }
     function ExportData()
    {
        filename='{{str_replace(" ","_",strtolower($report->name))}}_{{time()}}_{{$start->format("Y-m-d")}}_{{$end->format("Y-m-d")}}.xlsx';
        wb = XLSX.utils.book_new();
        @foreach($report->getTables() as $index=>$table)
        @if (count($processed[$table->name])>0)
        data=cleanData({!!json_encode($processed[$table->name])!!});
        ws = XLSX.utils.json_to_sheet(data);
        XLSX.utils.book_append_sheet(wb, ws, "{!!$table->name!!}");
        @endif
        @endforeach

XLSX.writeFile(wb,filename);
     }
</script>
