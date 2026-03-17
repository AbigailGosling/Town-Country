<?php
$isNew ??= false;
$isDelete ??= false;
?>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew == true)
                {{ __('Create Inbound Container')}}
            @elseif ($isDelete == true)
                {{ __('Delete Container: ') . $container->internal_number . __('?') }}
            @else
                {{ __('Edit Inbound Container: ') . $container->internal_number }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">

        @if ($isNew == true)
            <form method="POST" action="{{ route('containers.store') }}">
        @elseif ($isDelete == true)
            <form method="POST" action="{{ route('containers.delete', $container) }}">
            @method("delete")
        @else
            <form method="POST" action="{{ route('containers.update', $container) }}">
            @method("PUT")
        @endif
            @csrf
            <x-form>
                    <x-form-section title="Container Details" columns="2">

                    <!-- Internal Number -->
                    <div>
                        <x-input-label for="internal_number" :value="__('Internal Number')" />
                        <x-text-input id="internal_number" class="block mt-1 w-full" type="text"
                            name="internal_number" value="{{ old('internal_number', $container->internal_number) }}" required autofocus />
                        <x-input-error :messages="$errors->get('internal_number')" class="mt-2" />
                    </div>

                    <!-- Origin Port -->
                    <div>
                        <x-input-label for="origin_port" :value="__('Origin Port')" />
                        <x-text-input id="origin_port" class="block mt-1 w-full" type="text"
                            name="origin_port" value="{{ old('origin_port', $container->origin_port) }}" required />
                        <x-input-error :messages="$errors->get('origin_port')" class="mt-2" />
                    </div>

                    <!-- Origin Port -->
                    <div>
                        <x-input-label for="vessel" :value="__('Vessel')" />
                        <x-text-input id="vessel" class="block mt-1 w-full" type="text"
                            name="vessel" value="{{ old('vessel', $container->vessel) }}" required />
                        <x-input-error :messages="$errors->get('vessel')" class="mt-2" />
                    </div>

                    <!-- ETA -->
                    <div>
                        <x-input-label for="eta" :value="__('ETA')" />
                        <input id="eta" class="block mt-1 w-full" type="date"
                            name="eta" value="{{ old('eta', ($container->eta)?$container->eta->format('Y-m-d'):"") }}" required />
                        <x-input-error :messages="$errors->get('eta')" class="mt-2" />
                    </div>
                    <!-- Temperature -->
                    <div>
                        <x-input-label for="temperature_id" :value="__('Temperatures')" />
                        <select id="temperature_id" class="block mt-1 w-full" name="temperature_id" required>
                            <option disabled="disabled" selected value="">Select Temperature</option>
                            @foreach ($temperatures as $temperature)
                            <option {{($temperature->id==old('temperature_id', $container->temperature_id)) ? "selected":"";}} value="{{$temperature->id}}">{{$temperature->temperature}}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('temperature_id')" class="mt-2" />
                    </div>
                    <!-- Site -->
                     <div>
                        <x-input-label for="site_id" :value="__('Site')" />
                        <select id="site_id" class="block mt-1 w-full" name="site_id" required>
                            <option disabled="disabled" selected value="">Select Site</option>
                            @foreach ($sites as $site)
                            <option {{($site->id==old('site_id', $container->site_id)) ? "selected":"";}} value="{{$site->id}}">{{$site->name}}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                    </div>


                </x-form-section>

                <!-- Action Buttons -->
                <x-slot name="buttons">
                    @if ($isNew == true)
                    <x-form-button title="Create New Container" background="green" iconClass="fa-ship" :submit="true" />
                    @elseif ($isDelete == true)
                    <x-form-button title="Confirm Delete" background="red" iconClass="fa-trash" :submit="true" />
                    @else
                    <x-form-button title="Update Container" background="green" iconClass="fa-ship" :submit="true" />
                        @if ($container->admin_approved == true)
                            <x-form-button title="Admin Approved" background="green" iconClass="fa-check" route="inbound-approvals.create" :params="$container->id"/>
                        @else
                            <x-form-button title="Requires Admin Approval" background="red" iconClass="fa-circle-xmark" route="inbound-approvals.create" :params="$container->id"/>
                        @endif
                        @if ($container->arrived == true)
                            <x-form-button title="Container Arrived!" background="green" iconClass="fa-check"/>
                        @else
                            <x-form-button title="Mark Container Arrived" background="red" route="containers.arrive" :params="$container->id" iconClass="fa-circle-xmark"/>
                        @endif
                    @endif
                </x-slot>
            </x-form>
        </form>
        @if ($isNew == false && $isDelete == false)
        @if ($container->arrived == false)
        <div><a href="{{route('container-product.create',$container)}}">
            <div class="cursor-pointer bg-gradient-to-r from-green-500 to-green-600 flex rounded-md" style="width:150px;height:40px;float:right;margin-right:25px;margin-top:10px;">
                <div class="m-auto" style="vertical-align: center !important">
                    <span>
                        <i class="fa fa-plus text-white" style="font-size:12pt;"></i> <span style="color:white">Add a Product</span>
                    </span>
                </div>
            </div>
        </a>
        </div>
        @endif
        <x-data-table>
            <x-slot:headers>
                <x-data-table-header>Product</x-data-table-header>
                <x-data-table-header>Brand</x-data-table-header>
                <x-data-table-header>Cases</x-data-table-header>
                <x-data-table-header>Estimated KG</x-data-table-header>
                <x-data-table-header width="50">Actions</x-data-table-header>
            </x-slot:headers>
            <slot>
                @foreach ($containerProducts as $containerProduct)
                <tr>
                    <x-data-table-column>{{ $containerProduct->getProduct()?->getCut()->name??"Unknown" }}</x-data-table-column>
                    <x-data-table-column>{{ $brands[$containerProduct->getProduct()?->brand_id]->name??"Unknown" }}</x-data-table-column>
                    <x-data-table-column>{{ $containerProduct->getProduct()?->quantity??"Unknown" }}</x-data-table-column>
                    <x-data-table-column>{{ $containerProduct->getProduct()?->quantity * $containerProduct->getProduct()?->akg }}</x-data-table-column>
                    <td class="border-b dark:border-slate-600 p-2 pr-8">
                        <div class="grid grid-cols-3 gap-2">
                            <x-table-action-button route="container-product.edit" :id="['containerProduct'=>$containerProduct,'container'=>$container]">Edit</x-table-action-button>
                            <x-table-action-button route="container-product.predelete" type="delete" :id="['containerProduct'=>$containerProduct,'container'=>$container]"></x-table-action-button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </slot>
        </x-data-table>
        @endif
    </div>
</x-app-layout>
