<?php
$finalSummary = [];
if (!isset($intake_id)) $intake_id = "";
?>
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
            <x-form-section :columns="3">
                <div>
                    <x-input-label for="intake_id" :value="__('Intake ID')"/>
                    <x-text-input id="intake_id" class="block mt-1 w-full" type="number" name="intake_id" :value="old('intake_id',$intake_id)"/>
                    <x-input-error :messages="$errors->get('intake_id')" class="mt-2"/>
                </div>
                <div>
                </div>
            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
                <x-form-button title="Search" background="green" iconClass="fa-circle-arrow-right" :submit="true">
                </x-form-button>
            </x-slot>
    </x-form>
    </form>
    </div>
    <div class="py-12">
        <div class="bg-gray-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead style="width:100%; position: sticky; top: 0;"><tr class="py-12" style="width:100%;">Overview Table</tr>
                    <tr class="py-12" style="width:100%;" >
                <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Prod</th>
                <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Qty</th>
                <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">kg</th>
                <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Cost</th>
                <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Cost</th>
                <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Sub Total</th>
                <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Act Sub Total</th>
                </tr></thead>
                <tbody class="bg-white">
                    @if(isset($summary))
                    @foreach($summary as $row)
                    <tr>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{$row->name}}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{number_format($row->qty)}}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{number_format($row->kg,3)}}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{number_format($row->cost,3)}}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{number_format($row->actCost,3)}}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{number_format($row->subTotal,3)}}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{number_format($row->actSubTotal,3)}}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-sky-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-sky-200" style="width:100%; position: sticky; top: 0;"><tr class="py-12" style="width:100%;" >Sales</tr>
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
                    <?php $kg=$qty=$cost=$actCost=$sell=$profit=$actProfit=0;?>
                    @if(isset($saleInfo))
                    @foreach($saleInfo as $item)
                    <?php
                        $kg+=$item->kg;
                        $qty+=$item->qty;
                        $cost+=$item->cost;
                        $actCost+=$item->actCost;
                        $sell+=$item->sell;
                        $profit+=$item->profit;
                        $actProfit+=$item->profit;?>
                    <tr>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->salesperson }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->date }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->invoice_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->customer }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $intake_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->pallet_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->nationality_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->cooling_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->product_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->brand_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->supplier_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->unit }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ number_format($item->kg,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->cost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->actCost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->sell,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->profit,3) }}<br>@if ($item->cost!=0 && $item->profit !=0)
                            {{number_format($item->cost/$item->profit,3)}}%
                        @else
                            0.000%
                        @endif
                        </td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->actProfit,3) }}<br>@if ($item->actCost!=0 && $item->profit !=0)
                            {{number_format($item->actCost/$item->profit,3)}}%
                        @else
                            0.000%
                        @endif
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    <?php $finalSummary[] = [$kg,$qty,$cost,$actCost,$sell,$profit,$actProfit];?>
                </tbody>
                <tfoot class="bg-sky-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{$qty}}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($kg,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($cost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actCost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($sell,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($profit,3) }}<br>@if ($cost!=0 && $profit !=0)
                            {{number_format($cost/$profit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actProfit,3) }}<br>@if ($cost!=0 && $actProfit !=0)
                            {{number_format($cost/$actProfit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-orange-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-orange-200" style="width:100%; position: sticky; top: 0;"><tr class="py-12" style="width:100%;" >Returns</tr>
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Salesperson</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Date</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Invoice ID</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">Customer</th>
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
                <?php $kg=$qty=$cost=$actCost=$sell=$profit=$actProfit=0;?>
                <tbody class="bg-white">
                    @if(isset($creditInfo))
                    @foreach($creditInfo as $item)
                    <?php
                        $kg+=$item->kg;
                        $qty+=$item->qty;
                        $cost+=$item->cost;
                        $actCost+=$item->actCost;
                        $sell+=$item->sell;
                        $profit+=$item->profit;
                        $actProfit+=$item->profit;?>
                    <tr>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->salesperson }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->date }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->invoice_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->customer }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->new_intake_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->pallet_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->nationality_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->cooling_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->product_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->brand_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->supplier_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->unit }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ number_format($item->kg,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">-£{{ number_format($item->cost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">-£{{ number_format($item->actCost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">-£{{ number_format($item->sell,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">-£{{ number_format($item->profit,3) }}<br>@if ($item->cost!=0 && $item->profit!=0)
                            {{number_format($item->cost/$item->profit,3)}}%
                        @else
                            0.000%
                        @endif
                        </td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">-£{{ number_format($item->actProfit,3) }}<br>@if ($item->actCost!=0 && $item->profit!=0)
                            {{number_format($item->actCost/$item->profit,3)}}%
                        @else
                            0.000%
                        @endif
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    <?php $finalSummary[] = [$kg,$qty,$cost,$actCost,$sell,$profit,$actProfit];?>
                </tbody>
                <tfoot class="bg-orange-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{$qty}}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($kg,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($cost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actCost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($sell,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($profit,3) }}<br>@if ($cost!=0 && $profit !=0)
                            {{number_format($cost/$profit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actProfit,3) }}<br>@if ($cost!=0 && $actProfit !=0)
                            {{number_format($cost/$actProfit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-sky-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-sky-200" style="width:100%; position: sticky; top: 0;"><tr class="py-12" style="width:100%;" >Resales</tr>
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
                    <?php $kg=$qty=$cost=$actCost=$sell=$profit=$actProfit=0;?>
                    @if(isset($resaleInfo))
                    @foreach($resaleInfo as $item)
                    <?php
                        $kg+=$item->kg;
                        $qty+=$item->qty;
                        $cost+=$item->cost;
                        $actCost+=$item->actCost;
                        $sell+=$item->sell;
                        $profit+=$item->profit;
                        $actProfit+=$item->profit;?>
                    <tr>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->salesperson }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->date }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->invoice_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->customer }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $intake_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->pallet_id }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->nationality_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->cooling_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->product_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->brand_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->supplier_name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->unit }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ number_format($item->kg,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->cost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->actCost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->sell,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->profit,3) }}<br>@if ($item->cost!=0 && $item->profit !=0)
                            {{number_format($item->cost/$item->profit,3)}}%
                        @else
                            0.000%
                        @endif
                        </td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->actProfit,3) }}<br>@if ($item->actCost!=0 && $item->profit !=0)
                            {{number_format($item->actCost/$item->profit,3)}}%
                        @else
                            0.000%
                        @endif
                        </td>
                    </tr>
                    @endforeach
                    @endif
                    <?php $finalSummary[] = [$kg,$qty,$cost,$actCost,$sell,$profit,$actProfit];?>
                </tbody>
                <tfoot class="bg-sky-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{$qty}}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($kg,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($cost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actCost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($sell,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($profit,3) }}<br>@if ($cost!=0 && $profit !=0)
                            {{number_format($cost/$profit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actProfit,3) }}<br>@if ($cost!=0 && $actProfit !=0)
                            {{number_format($cost/$actProfit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-red-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-red-200" style="width:100%; position: sticky; top: 0;"><tr class="py-12" style="width:100%;" >Summary</tr>
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
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
                <?php
                $kg = array_sum(array_column($finalSummary,0));
                $qty = array_sum(array_column($finalSummary,1));
                $cost = array_sum(array_column($finalSummary,2));
                $actCost = array_sum(array_column($finalSummary,3));
                $sell = array_sum(array_column($finalSummary,4));
                $profit = array_sum(array_column($finalSummary,5));
                $actProfit = array_sum(array_column($finalSummary,6));?>
                <tfoot class="bg-red-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{$qty}}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($kg,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($cost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actCost,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($sell,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($profit,3) }}<br>@if ($cost!=0 && $profit !=0)
                            {{number_format($cost/$profit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actProfit,3) }}<br>@if ($cost!=0 && $actProfit !=0)
                            {{number_format($cost/$actProfit,3)}}%
                        @else
                            0.000%
                        @endif</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="py-12">
        <div class="bg-red-200 shadow-sm sm:rounded-lg ml-6 mr-6" style="width:100%;">
            <table class="text-sm mt-4" style="width:100%;">
                <thead class="bg-red-200" style="width:100%; position: sticky; top: 0;"><tr class="py-12" style="width:100%;" >Remaining Stock</tr>
                    <tr class="py-12" style="width:100%;">
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
                    <?php $kg=$qty=$cost=$actCost=$subTotal=$actSubTotal=0;?>
                    @if(isset($stockInfo))
                    @foreach($stockInfo as $item)
                    <?php
                        $kg+=$item->kg;
                        $qty+=$item->qty;
                        $subTotal+=$item->subTotal;
                        $actSubTotal+=$item->actSubTotal;?>
                    <tr>

                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->name }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ $item->qty }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">{{ number_format($item->kg,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->cost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->actCost,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->subTotal,3) }}</td>
                        <td style="width:100px;word-wrap:break-word;white-space:-moz-pre-wrap;white-space:pre-wrap;" align="center">£{{ number_format($item->actSubTotal,3) }}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
                <tfoot class="bg-red-200" style="width:100%; position: sticky; bottom: 0;">
                    <tr class="py-12" style="width:100%;">
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{$qty}}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">{{ number_format($kg,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center"></th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($subTotal,3) }}</th>
                        <th class="max-w-full border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">£{{ number_format($actSubTotal,3) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
