<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
                Create New Vehicle
            @else
                {{ 'Vehicle : ' . $vehicle->reg }}
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
            <form method="POST" action="{{route('vehicles.store')}}">
            {{ method_field('POST') }}
    @else
            <form method="POST" action="{{route('vehicles.update', ['vehicle' => $vehicle->id])}}">
            {{ method_field('PUT') }}
    @endif
    @csrf
        <x-form>
            <x-form-section title="Details" columns="1">
                <div>
                    <x-input-label for="reg" :value="__('Registration')"/>
                    <x-text-input id="reg" class="block mt-1 w-full" type="text" name="reg"
                                  :value="old('reg', $vehicle->reg)"
                                  required autofocus/>
                    <x-input-error :messages="$errors->get('reg')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="vehicle_type_id" :value="__('Vehicle Type')"/>
                    <select id="vehicle_type_id" name="vehicle_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select a type</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{$type->id}}" @if((string) old('vehicle_type_id', $vehicle->vehicle_type_id) === (string) $type->id) selected @endif>
                                {{$type->name}}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('vehicle_type_id')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="make" :value="__('Make')"/>
                    <x-text-input id="make" class="block mt-1 w-full" type="text" name="make"
                                  :value="old('make', $vehicle->make)"/>
                    <x-input-error :messages="$errors->get('make')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="model" :value="__('Model')"/>
                    <x-text-input id="model" class="block mt-1 w-full" type="text" name="model"
                                  :value="old('model', $vehicle->model)"/>
                    <x-input-error :messages="$errors->get('model')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="grossWeight" :value="__('Gross Weight')"/>
                    <x-text-input id="grossWeight" class="block mt-1 w-full" type="text" name="grossWeight"
                                  :value="old('grossWeight', $vehicle->grossWeight)"/>
                    <x-input-error :messages="$errors->get('grossWeight')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="payload" :value="__('Payload')"/>
                    <x-text-input id="payload" class="block mt-1 w-full" type="text" name="payload"
                                  :value="old('payload', $vehicle->payload)"/>
                    <x-input-error :messages="$errors->get('payload')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="site_id" :value="__('Site')"/>
                    <select id="site_id" name="site_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Select a site</option>
                        @foreach($sites as $site)
                            <option value="{{$site->id}}" @if((string) old('site_id', $vehicle->site_id) === (string) $site->id) selected @endif>
                                {{$site->name}}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('site_id')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="driver" :value="__('Driver')"/>
                    <x-text-input id="driver" class="block mt-1 w-full" type="text" name="driver"
                                  :value="old('driver', $vehicle->driver)"/>
                    <x-input-error :messages="$errors->get('driver')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="max_pallet_rows" :value="__('Max Pallet Rows')"/>
                    <x-text-input id="max_pallet_rows" class="block mt-1 w-full" type="number" min="1" max="40" name="max_pallet_rows"
                                  :value="old('max_pallet_rows', $vehicle->max_pallet_rows ?? 5)"/>
                    <x-input-error :messages="$errors->get('max_pallet_rows')" class="mt-2"/>
                </div>
            </x-form-section>

            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create Vehicle' : 'Save Vehicle' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    </x-slot>
    </x-form>
</form>
    </div>
</x-app-layout>
