<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
                Create New Site
            @else
                {{ "Site : " . $site->name }}
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
            <form method="POST" action="{{route('sites.store')}}">
            {{ method_field('POST') }}
    @else
            <form method="POST" action="{{route('sites.update', ['site' => $site->id])}}">
            {{ method_field('PUT') }}
    @endif
    @csrf
        <x-form>
            <x-form-section title="Details" columns="1">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" :value="__('Name')"/>

                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                  :value="old('name', $site->name)"
                                  required autofocus/>

                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>
                <!-- Abbreviation Field -->
                <div>
                    <x-input-label for="abbr" :value="__('Abbreviation')"/>

                    <x-text-input id="abbr" class="block mt-1 w-full" type="text" name="abbr"
                                  :value="old('abbr', $site->abbreviation)" required/>

                    <x-input-error :messages="$errors->get('abbr')" class="mt-2"/>
                </div>
                <!-- Next Day Cutoff Time -->
                <div>
                    <x-input-label for="cutoff" :value="__('Next Day Cutoff Time')"/>

                    <x-text-input id="cutoff" class="block mt-1 w-full" type="time" name="cutoff"
                                  :value="old('cutoff',  $site->cutoff)" required/>

                    <x-input-error :messages="$errors->get('cutoff')" class="mt-2"/>
                </div>
                <div class="mt-4" for="sale_blocked" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="sale_blocked" name="sale_blocked"
                            @if ($site->sale_blocked) checked @endif />
                    <div style="width: 1em;"></div>
                    <x-input-label for="sale_blocked" value="Sale Blocked"/>
                </div>
                <div class="mt-4" for="disabled" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="disabled" name="disabled"
                            @if ($site->disabled) checked @endif />
                    <div style="width: 1em;"></div>
                    <x-input-label for="disabled" value="Site Disabled"/>
                </div>

            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create Site' : 'Save Site' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    @if (!$isNew) <div>
    <x-form-button title="{{ 'Create Location' }}" iconClass="fa-pencil" background="orange" route="locations.create" :params="$site->id">
    </x-form-button>
    <x-form-button title="{{ 'Create Movement Rule' }}" iconClass="fa-pencil" background="orange" route="stockmovements.create" :params="$site->id">
    </x-form-button></div>@endif
    </x-slot>
    </x-form>
    <x-data-table>
        <x-slot:headers>
            <x-data-table-header>Movement Rules</x-data-table-header>
            <x-data-table-header>Day(s) Lead Time</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Created At</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Updated At</x-data-table-header>
            <x-data-table-header></x-data-table-header>
        </x-slot:headers>
        <slot>
            @foreach($movements as $movement)
                <tr>
                    <x-data-table-column>{{$movement->getDestination()->name}}</x-data-table-column>
                    <x-data-table-column>{{$movement->days}}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">{{$movement->created_at->format('d/m/Y')}}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">{{$movement->updated_at->format('d/m/Y')}}</x-data-table-column>
                    <td align="right" class="border-b dark:border-slate-600 p-2 pr-8">
                        <div class="grid-cols-2">
                            <div class="grid grid-cols-1">
                                <div>
                                    <a href="{{route('stockmovements.edit', ['stockmovementrule'=>$movement->id,])}}">
                                        <button type="button" class="rounded bg-green-500 hover:bg-green-700 w-6 h-6" href=""><i class="fas fa-edit text-green-100"></i></button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </slot>
    </x-data-table>
    <x-data-table>
        <x-slot:headers>
            <x-data-table-header :show-on-mobile="false">Location Name</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Created At</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Updated At</x-data-table-header>
            <x-data-table-header></x-data-table-header>
        </x-slot:headers>
        <slot>
            @foreach($locations as $location)
                <tr>
                    <x-data-table-column :show-on-mobile="false">{{$location->name}}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">{{$location->created_at->format('d/m/Y')}}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">{{$location->updated_at->format('d/m/Y')}}</x-data-table-column>
                    <td align="right" class="border-b dark:border-slate-600 p-2 pr-8">
                        <div class="grid-cols-2">
                            <div class="grid grid-cols-1">
                                <div>
                                    <a href="{{route('locations.edit', ['site'=>$site->id,'location'=>$location->id])}}">
                                        <button type="button" class="rounded bg-green-500 hover:bg-green-700 w-6 h-6" href=""><i class="fas fa-edit text-green-100"></i></button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </slot>
    </x-data-table>
    </div>
</x-app-layout>
