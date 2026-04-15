<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        </h2>
    </x-slot>
    <div align="left">
    <form align="left" method="POST" action="{{route('intake_report.show')}}">
    {{ method_field('POST') }}
    @csrf
        <x-form>
            <x-form-section :columns="2">
                <div>
                    <x-input-label for="intake_id_from" :value="__('Intake ID From')"/>
                    <x-text-input id="intake_id_from" class="block mt-1 w-full" type="number" name="intake_id_from" :value="old('intake_id_from',$intake_id_from)"/>
                    <x-input-error :messages="$errors->get('intake_id_from')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="intake_id_to" :value="__('Intake ID To')"/>
                    <x-text-input id="intake_id_to" class="block mt-1 w-full" type="number" name="intake_id_to" :value="old('intake_id_to',$intake_id_to)"/>
                    <x-input-error :messages="$errors->get('intake_id_to')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="date_from" :value="__('Date From')"/>
                    <x-text-input id="date_from" class="block mt-1 w-full" type="date" name="date_from" :value="old('date_from',$date_from)"/>
                    <x-input-error :messages="$errors->get('date_from')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="date_to" :value="__('Date To')"/>
                    <x-text-input id="date_to" class="block mt-1 w-full" type="date" name="date_to" :value="old('date_to',$date_to)"/>
                    <x-input-error :messages="$errors->get('date_to')" class="mt-2"/>
                </div>
            </x-form-section>
            <div class="px-6 pb-4 text-sm text-gray-600">
                Use either an intake ID range or a date range.
            </div>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
                <x-form-button title="Search" background="green" iconClass="fa-circle-arrow-right" :submit="true">
                </x-form-button>
                <x-form-button id="export" title="Export Excel" background="green" iconClass="fa-file-spreadsheet" :disable="count($resolved_intake_ids) === 0">
                </x-form-button>
            </x-slot>
    </x-form>
    </form>
    </div>
    @if(isset($report_scope))
    <div class="ml-6 mr-6 mt-4 rounded bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-start">
            <div>
        <div><strong>Report Scope:</strong> {{ $report_scope }}</div>
        @if(count($resolved_intake_ids) > 0)
        <div class="mt-1"><strong>Reported Intake IDs:</strong> {{ $reportedIntakeRange }}</div>
        @endif
            </div>
        </div>
    </div>
    @endif
    @php
        $currency = fn ($value, $negate = false) => ($negate ? '-' : '') . '£' . number_format((float) $value, 3);
        $percent = function ($numerator, $denominator) {
            if ((float) $denominator === 0.0 || (float) $numerator === 0.0) {
                return '0.000%';
            }

            return number_format(((float) $numerator / (float) $denominator) * 100, 3) . '%';
        };
    @endphp
    <div class="py-12">
        <div class="bg-gray-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead style="width:100%; position: sticky; top: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th colspan="8" class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Overview Table</th>
                    </tr>
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Intake ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Prod</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Qty</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">kg</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sub Total</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Sub Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($summary as $row)
                    <tr>
                        <td style="width:100px;" align="center">{{ $row->intake_id }}</td>
                        <td style="width:100px;" align="center">{{ $row->name }}</td>
                        <td style="width:100px;" align="center">{{ number_format($row->qty) }}</td>
                        <td style="width:100px;" align="center">{{ number_format($row->kg, 3) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($row->cost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($row->actCost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($row->subTotal) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($row->actSubTotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($overviewTotals['qty'], 0) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($overviewTotals['kg'], 3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($overviewTotals['cost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($overviewTotals['actCost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($overviewTotals['subTotal']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($overviewTotals['actSubTotal']) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-sky-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-sky-200" style="width:100%; position: sticky; top: 0;">
                    <tr class="py-12" style="width:100%;"><th colspan="19" class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sales</th></tr>
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Salesperson</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Date</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Inv ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Customer</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Intake ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Pallet ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Nationality</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Temperature</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Prod Name</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Brand</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Supplier</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Qty</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Unit</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">kg</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sell</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Profit</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($saleInfo as $item)
                    <tr>
                        <td style="width:100px;" align="center">{{ $item->salesperson }}</td>
                        <td style="width:100px;" align="center">{{ $item->date }}</td>
                        <td style="width:100px;" align="center">{{ $item->invoice_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->customer }}</td>
                        <td style="width:100px;" align="center">{{ $item->intake_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->pallet_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->nationality_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->cooling_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->product_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->brand_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->supplier_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;" align="center">{{ $item->unit }}</td>
                        <td style="width:100px;" align="center">{{ number_format($item->kg, 3) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->cost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->actCost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->sell) }}</td>
                        <td style="width:100px;" align="center">{!! $currency($item->profit) . '<br>' . $percent($item->profit, $item->cost) !!}</td>
                        <td style="width:100px;" align="center">{!! $currency($item->actProfit) . '<br>' . $percent($item->actProfit, $item->actCost) !!}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-sky-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        @for($i = 0; $i < 11; $i++)
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        @endfor
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $salesTotals['qty'] }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($salesTotals['kg'], 3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($salesTotals['cost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($salesTotals['actCost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($salesTotals['sell']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($salesTotals['profit']) . '<br>' . $percent($salesTotals['profit'], $salesTotals['cost']) !!}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($salesTotals['actProfit']) . '<br>' . $percent($salesTotals['actProfit'], $salesTotals['actCost']) !!}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-orange-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-orange-200" style="width:100%; position: sticky; top: 0;">
                    <tr class="py-12" style="width:100%;"><th colspan="20" class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Returns</th></tr>
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Salesperson</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Date</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Invoice ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Customer</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Intake ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">New Intake ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Pallet ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Nationality</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Temperature</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Prod Name</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Brand</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Supplier</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Qty</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Unit</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">kg</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sell</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Profit</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($creditInfo as $item)
                    <tr>
                        <td style="width:100px;" align="center">{{ $item->salesperson }}</td>
                        <td style="width:100px;" align="center">{{ $item->date }}</td>
                        <td style="width:100px;" align="center">{{ $item->invoice_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->customer }}</td>
                        <td style="width:100px;" align="center">{{ $item->intake_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->new_intake_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->pallet_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->nationality_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->cooling_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->product_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->brand_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->supplier_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;" align="center">{{ $item->unit }}</td>
                        <td style="width:100px;" align="center">{{ number_format($item->kg, 3) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->cost, true) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->actCost, true) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->sell, true) }}</td>
                        <td style="width:100px;" align="center">{!! $currency($item->profit) . '<br>' . $percent($item->profit, $item->cost) !!}</td>
                        <td style="width:100px;" align="center">{!! $currency($item->actProfit) . '<br>' . $percent($item->actProfit, $item->actCost) !!}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-orange-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        @for($i = 0; $i < 12; $i++)
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        @endfor
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $returnTotals['qty'] }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($returnTotals['kg'], 3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($returnTotals['cost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($returnTotals['actCost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($returnTotals['sell']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($returnTotals['profit']) . '<br>' . $percent($returnTotals['profit'], $returnTotals['cost']) !!}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($returnTotals['actProfit']) . '<br>' . $percent($returnTotals['actProfit'], $returnTotals['actCost']) !!}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-sky-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-sky-200" style="width:100%; position: sticky; top: 0;">
                    <tr class="py-12" style="width:100%;"><th colspan="19" class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Resales</th></tr>
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Salesperson</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Date</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Inv ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Customer</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Intake ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Pallet ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Nationality</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Temperature</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Prod Name</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Brand</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Supplier</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Qty</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Unit</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">kg</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sell</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Profit</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Profit</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($resaleInfo as $item)
                    <tr>
                        <td style="width:100px;" align="center">{{ $item->salesperson }}</td>
                        <td style="width:100px;" align="center">{{ $item->date }}</td>
                        <td style="width:100px;" align="center">{{ $item->invoice_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->customer }}</td>
                        <td style="width:100px;" align="center">{{ $item->intake_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->pallet_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->nationality_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->cooling_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->product_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->brand_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->supplier_name }}</td>
                        <td style="width:100px;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;" align="center">{{ $item->unit }}</td>
                        <td style="width:100px;" align="center">{{ number_format($item->kg, 3) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->cost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->actCost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->sell) }}</td>
                        <td style="width:100px;" align="center">{!! $currency($item->profit) . '<br>' . $percent($item->profit, $item->cost) !!}</td>
                        <td style="width:100px;" align="center">{!! $currency($item->actProfit) . '<br>' . $percent($item->actProfit, $item->actCost) !!}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-sky-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        @for($i = 0; $i < 11; $i++)
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        @endfor
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $resaleTotals['qty'] }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($resaleTotals['kg'], 3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($resaleTotals['cost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($resaleTotals['actCost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($resaleTotals['sell']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($resaleTotals['profit']) . '<br>' . $percent($resaleTotals['profit'], $resaleTotals['cost']) !!}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($resaleTotals['actProfit']) . '<br>' . $percent($resaleTotals['actProfit'], $resaleTotals['actCost']) !!}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-red-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-red-200" style="width:100%; position: sticky; top: 0;">
                    <tr class="py-12" style="width:100%;"><th colspan="19" class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Summary</th></tr>
                    <tr class="py-12" style="width:100%;">
                        @for($i = 0; $i < 11; $i++)
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        @endfor
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Qty</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">kg</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sell</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Profit</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Profit</th>
                    </tr>
                </thead>
                <tfoot class="bg-red-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        @for($i = 0; $i < 11; $i++)
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        @endfor
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $finalSummaryTotals['qty'] }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($finalSummaryTotals['kg'], 3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($finalSummaryTotals['cost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($finalSummaryTotals['actCost']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($finalSummaryTotals['sell']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($finalSummaryTotals['profit']) . '<br>' . $percent($finalSummaryTotals['profit'], $finalSummaryTotals['cost']) !!}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{!! $currency($finalSummaryTotals['actProfit']) . '<br>' . $percent($finalSummaryTotals['actProfit'], $finalSummaryTotals['actCost']) !!}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-red-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-red-200" style="width:100%; position: sticky; top: 0;">
                    <tr class="py-12" style="width:100%;"><th colspan="8" class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Remaining Stock</th></tr>
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Intake ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Prod</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Qty</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">kg</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Cost</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sub Total</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Sub Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @foreach($stockInfo as $item)
                    <tr>
                        <td style="width:100px;" align="center">{{ $item->intake_id }}</td>
                        <td style="width:100px;" align="center">{{ $item->name }}</td>
                        <td style="width:100px;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;" align="center">{{ number_format($item->kg, 3) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->cost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->actCost) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->subTotal) }}</td>
                        <td style="width:100px;" align="center">{{ $currency($item->actSubTotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-red-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $stockTotals['qty'] }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($stockTotals['kg'], 3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($stockTotals['subTotal']) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ $currency($stockTotals['actSubTotal']) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
@stack('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.1/xlsx.full.min.js"></script>
<script>
    const intakeReportSheets = @json($exportSheets);

    function exportIntakeReport() {
        if (!Object.keys(intakeReportSheets).length) {
            return;
        }

        const workbook = XLSX.utils.book_new();
        Object.entries(intakeReportSheets).forEach(([sheetName, rows]) => {
            const worksheet = XLSX.utils.json_to_sheet(rows);
            XLSX.utils.book_append_sheet(workbook, worksheet, sheetName.substring(0, 31));
        });

        const safeScope = ('{{ $report_scope ?? 'intake-report' }}')
            .replace(/[^a-z0-9]+/gi, '-')
            .replace(/^-+|-+$/g, '')
            .toLowerCase();
        XLSX.writeFile(workbook, `${safeScope || 'intake-report'}.xlsx`);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const exportButton = document.getElementById('export');
        if (!exportButton) {
            return;
        }

        exportButton.addEventListener('click', exportIntakeReport);
    });
</script>
