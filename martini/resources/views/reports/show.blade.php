<?php
global $processed;
$processed = [];
$cutgroupsSorted =[];
$footerResults=[];

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
        <x-xlform>
            <x-xlform-section>
            </x-xlform-section>
            <x-xlform-section>
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
                    <x-input-label for="selector" :value="__('View')"/>
                    <select class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full' id="selector" name="selector">
                        @foreach($reports as $item)
                            <option value="{{$item->id}}" @if($item->id == $report->id) selected @endif>{{$item->name}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('selector')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="user_id" :value="__('User')"/>
                    <select id="user_id" name="user_id" class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full'>
                        <option value="" selected></option>
                        @foreach ($users->sortBy("name") as $user)
                        <option value="{{$user->id}}" @if ($user_id==$user->id) selected @endif>{{$user->name}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('user_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="customer_id" :value="__('Customer')"/>
                    <select id="customer_id" name="customer_id" class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full'>
                        <option value="" selected></option>
                        @foreach ($customers->sortBy("businessname") as $customer)
                        @if ($customer->businessname!="")
                        <option value="{{$customer->id}}" @if ($customer_id==$customer->id) selected @endif>{{$customer->businessname}}</option>
                        @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('customer_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="picksheet_id" :value="__('Invoice ID')"/>
                    <x-text-input id="picksheet_id" class="block mt-1 w-full" type="number" name="pickersheet_id" :value="$pickersheet_id"/>
                    <x-input-error :messages="$errors->get('picksheet_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="intake_id" :value="__('Intake ID')"/>
                    <x-text-input id="intake_id" class="block mt-1 w-full" type="number" name="intake_id" :value="$intake_id"/>
                    <x-input-error :messages="$errors->get('intake_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="pallet_id" :value="__('Pallet ID')"/>
                    <x-text-input id="pallet_id" class="block mt-1 w-full" type="number" name="pallet_id" :value="$pallet_id"/>
                    <x-input-error :messages="$errors->get('pallet_id')" class="mt-2"/>
                </div>
            </x-xlform-section>
            <x-xlform-section>
                <div>
                    <x-input-label for="species_id" :value="__('Species')"/>
                    <select class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full' id="species_id" name="species_id">
                        <option value="" selected></option>
                        @foreach ($species->sortBy("name") as $specie)
                        <option value="{{$specie->id}}" @if ($species_id==$specie->id) selected @endif>{{$specie->name}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('species_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="cutgroup_id" :value="__('Cut Group')"/>
                    <select class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full' id="cutgroup_id" name="cutgroup_id">
                        <option value="" selected></option>
                        @foreach ($cut_groups->sortBy("name") as $cut_group)
                        <?php
                            if (!array_key_exists($cut_group->species_id,$cutgroupsSorted)) $cutgroupsSorted[$cut_group->species_id] = [];
                            $cutgroupsSorted[$cut_group->species_id][$cut_group->id]=$cut_group->name;
                        ?>
                        @if ($species_id==$cut_group->species_id)
                        <option value="{{$cut_group->id}}" @if ($cutgroup_id==$cut_group->id) selected @endif>{{$cut_group->name}}</option>
                        @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('cut_group')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="brand_id" :value="__('Brand')"/>
                    <select class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full' id="brand_id" name="brand_id">
                        <option value="" selected></option>
                        @foreach ($brands->sortBy("name") as $brand)
                        @if($brand->name!="")
                        <option value="{{$brand->id}}" @if ($brand_id==$brand->id) selected @endif>{{$brand->name}}</option>
                        @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('brand_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="supplier_id" :value="__('Supplier')"/>
                    <select id="supplier_id" name="supplier_id" class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full'>
                        <option value="" selected></option>
                        @foreach ($suppliers->sortBy("name") as $supplier)
                        @if($supplier->name!="")
                        <option value="{{$supplier->id}}" @if ($supplier_id==$supplier->id) selected @endif>{{$supplier->name}}</option>
                        @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('supplier_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="nationality_id" :value="__('Nationality')"/>
                    <select id="nationality_id" name="nationality_id" class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full'>
                        <option value="" selected></option>
                        @foreach ($nationalities->sortBy("name") as $nationality)
                        @if ($nationality->name != "")
                        <option value="{{$nationality->id}}" @if ($nationality_id==$nationality->id) selected @endif>{{$nationality->name}}</option>
                        @endif
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('nationality_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="cooling_id" :value="__('Tempurature')"/>
                    <select id="cooling_id" name="cooling_id" class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full'>
                        <option value="" selected></option>
                        @foreach ($tempuratures->sortBy("name") as $tempurature)
                        <option value="{{$tempurature->id}}" @if ($cooling_id==$tempurature->id) selected @endif>{{$tempurature->temperature}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('cooling_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="health_id" :value="__('Health Mark')"/>
                    <select id="health_id" name="health_id" class='rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block mt-1 w-full'>
                        <option value="" selected></option>
                        @foreach ($health_marks->sortBy("name") as $health_mark)
                        <option value="{{$health_mark->id}}" @if ($health_id==$health_mark->id) selected @endif>{{$health_mark->name}}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('health_id')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="internal_num" :value="__('T&C Number')"/>
                    <x-text-input id="internal_num" class="block mt-1 w-full" type="text" name="internal_num" :value="$internal_num"/>
                    <x-input-error :messages="$errors->get('internal_num')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="import_num" :value="__('Customs Import Entry')"/>
                    <x-text-input id="customs" class="block mt-1 w-full" type="text" name="import_num" :value="$import_num"/>
                    <x-input-error :messages="$errors->get('import_num')" class="mt-2"/>
                </div>
            </x-xlform-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
                <x-form-button title="Search" background="green" iconClass="fa-circle-arrow-right" :submit="true">
                </x-form-button>
                <x-form-button id="export" title="Loading..." background="green" iconClass="fa-file-spreadsheet" :disable="true">
                </x-form-button>
            </x-slot>
    </x-xlform>
    </form>
    </div>
    <div>
        @foreach($report->getTables() as $index=>$table)
        <div style="width:100%" class="bg-gray-200 shadow-sm sm:rounded-lg ml-6 mr-6">
            <table class="table-fixed w-fit text-sm mt-4">
                <?php
                    $columns =$table->getColumns();
                    $processed[$table->name] = [];
                    $data = $dataRanges[$index];
                    $tablenameSimplified = preg_replace("/\W|_/", '', strtolower($table->name));
                    if(count($data)==0 && $index!=0)continue;
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
                        <td style="width:100px" align="center"><input style="width:100%" type="number" step="0.01" pattern="^\d*(\.\d{0,2})?$" onpaste="changed(this)" oncut="changed(this)" onkeyup="changed(this)" id="{{$fieldName}}" name="{{$fieldName}}" og="{{$d->$col}}">{{$t}}</input></td>
                        @else
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center" id="{{$fieldName}}" og="{{$d->$col}}">{{$t}}</td>
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
                    <?php
                    $h = App\Helpers\ReportHelper::resolveHeader($column,$table->mode);
                    $t = App\Helpers\ReportHelper::resolveFooter($column,$processed[$table->name],$table->mode);
                    $footerResult[$h] = preg_replace("/[£,]/", '',$t);
                    ?>
                    <x-data-table-header>{{$t}}
                    @if (stripos($h,"Profit")!==false)
                    <?php
                        $percision = 3;
	                    $magShift = pow(10,$percision);
                        $target = (stripos($h,"Actual"))?"Actual Cost Value":"Cost Value";
                        $rollingProfit = filter_var($footerResult[$h], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
                        $rollingProfit = App\Helpers\ReportHelper::floorDec(floatval($rollingProfit)*$magShift,0)/$magShift;
                        $rollingCost = filter_var($footerResult[$target], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND);
			            $rollingCost = App\Helpers\ReportHelper::floorDec(floatval($rollingCost)*$magShift,0)/$magShift;
                        if ($rollingProfit==0 && $rollingCost==0)
                        {
                            $percentage = "0.000";
                        }
                        else
                        {
                            $profitRatio = $rollingProfit/$rollingCost;
                            $percentage = App\Helpers\ReportHelper::floorDec($profitRatio*100,3);
                        }
                    ?>
                     {{$percentage}}%
                    @endif
                    </x-data-table-header>
                    @endforeach
                </tr></tfoot>
            </table>
        </div>
        @endforeach
    </div>

</x-app-layout>
@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script>
    setTimeout(readyEvent, 1);
    let cutrgoupsSorted = {!!json_encode($cutgroupsSorted)!!}

     function readyEvent() {
        $("#export").css("pointer-events","auto");
        $("#export-text").text(" Export ");
        $("#species_id").on('change',speciesIDChanged);
     }

     function speciesIDChanged(event){
        console.log(event.target.value);
        let cutSelector = $("#cutgroup_id");
        cutSelector.empty();
        cutSelector.append($("<option></option>").attr("value", "").text("").attr("disabled", true).attr("selected", true));
        $.each(cutrgoupsSorted[event.target.value], function(key,value) {
            cutSelector.append($("<option></option>").attr("value", key).text(value));
        });
        console.log(event.target.value);
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
