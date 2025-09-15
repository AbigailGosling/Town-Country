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

                    <!-- ETA -->
                    <div>
                        <x-input-label for="eta" :value="__('ETA')" />
                        <input id="eta" class="block mt-1 w-full" type="date"
                            name="eta" value="{{ old('eta', ($container->eta)?$container->eta->format('Y-m-d'):"") }}" required />
                        <x-input-error :messages="$errors->get('eta')" class="mt-2" />
                    </div>
                </x-form-section>

                <!-- Action Buttons -->
                <x-slot name="buttons">
                    @if ($isNew == true)
                    <x-form-button title="Create Container" background="green" iconClass="fa-save" :submit="true" />
                    @else
                    <x-form-button title="Update Container" background="green" iconClass="fa-save" :submit="true" />
                        @if ($container->admin_approved == true)
                            <x-form-button title="Admin Approval" background="orange" iconClass="fa-check" route="inbound-approvals.create" :params="$container->id"/>
                        @else
                            <x-form-button title="Admin Approval" background="red" iconClass="fa-circle-xmark" route="inbound-approvals.create" :params="$container->id"/>
                        @endif
                        @if ($container->arrived == true)
                            <x-form-button title="Container Arrived!" background="orange" iconClass="fa-check-double"/>
                        @else
                            <x-form-button title="Mark Container Arrived" background="red" route="containers.arrive" :params="$container->id" iconClass="fa-xmark"/>
                        @endif
                    @endif
                </x-slot>
            </x-form>
        </form>
        @if ($isNew == false)
        <x-data-table>
            <x-slot:headers>
                <x-data-table-header>Product</x-data-table-header>
                <x-data-table-header>Cases</x-data-table-header>
                <x-data-table-header>Estimated KG</x-data-table-header>
                <x-data-table-header>
                    <a href="{{route('container-product.create',$container)}}"><div class="cursor-pointer bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center rounded-md">
                        <div class="m-auto" style="vertical-align: center !important">
                            <span>
                                <i class="fa fa-plus fa-lg text-white"></i>
                            </span>
                        </div>
                    </div></a>
                </x-data-table-header>
            </x-slot:headers>
            <slot>
                @foreach ($container->containerProducts()->get() as $cp)
                <tr>
                    <x-data-table-column>{{ $cp->getProduct()?->getCut()->name }}</x-data-table-column>
                    <x-data-table-column>{{ $cp->getProduct()?->quantity }}</x-data-table-column>
                    <x-data-table-column>{{ $cp->getProduct()?->quantity * $cp->getProduct()?->akg }}</x-data-table-column>
                    <td class="border-b dark:border-slate-600 p-2 pr-8">
                        <div class="grid grid-cols-2 gap-2">
                            <x-table-action-button route="container-product.edit" :id="['product'=>$cp->id,'container'=>$container]" />
                        </div>
                    </td>
                </tr>
                @endforeach
            </slot>
        </x-data-table>
        @endif
    </div>
</x-app-layout>
