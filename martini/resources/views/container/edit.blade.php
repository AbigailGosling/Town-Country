<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew == false)
                {{ __('Edit Inbound Container: ') . $container->internal_number }}
            @else
                {{ __('Create Inbound Container')}}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">

        @if ($isNew == false)
            <form method="POST" action="{{ route('containers.update', $container) }}">
            @method("PUT")
        @else
            <form method="POST" action="{{ route('containers.store') }}">
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
                     <!-- Brand -->
                     <div>
                        <x-input-label for="temperature_id" :value="__('Temperatures')" />
                        <select id="temperature_id" class="block mt-1 w-full" name="temperature_id" required>
                            <option disabled="disabled" selected value="">Select Temperature</option>
                            @foreach ($temperatures as $temperature)
                            <option {{($temperature->id==old('temperature_id', $container->temperature_id)) ? "selected":"";}} value="{{$temperature->id}}" {{{($isNew==false&&$container->temperature_id > 0)?'disabled="disabled"':''}}}>{{$temperature->temperature}}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('temperature_id')" class="mt-2" />
                    </div>

                </x-form-section>

                <!-- Action Buttons -->
                <x-slot name="buttons">
                    @if ($isNew == true)
                    <x-form-button title="Create New Container" background="green" iconClass="fa-ship" :submit="true" />
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
        @if ($isNew == false)
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
                <x-data-table-header style="background-color: red;">Cases</x-data-table-header>
                <x-data-table-header>Estimated KG</x-data-table-header>
                <x-data-table-header width="50">Actions</x-data-table-header>
            </x-slot:headers>
            <slot>
                @foreach ($container->containerProducts()->get() as $cp)
                <tr>
                    <x-data-table-column>{{ $cp->getProduct()?->getCut()->name }}</x-data-table-column>
                    <x-data-table-column>{{ $cp->getProduct()?->quantity }}</x-data-table-column>
                    <x-data-table-column>{{ $cp->getProduct()?->quantity * $cp->getProduct()?->akg }}</x-data-table-column>
                    <x-data-table-column>
                        <x-table-action-button route="container-product.edit" :id="['containerProduct'=>$cp,'container'=>$container]">Edit</x-table-action-button>
                    </x-data-table-column>
                </tr>
                @endforeach
            </slot>
        </x-data-table>
        @endif
    </div>
</x-app-layout>
