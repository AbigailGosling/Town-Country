<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
            {{ $site->name. " : Creatue New Locaiton" }}
            @else
                {{ $site->name. " : ".$location->name }}
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
            <form method="POST" action="{{route('locations.store', ['site'=>$site])}}">
            {{ method_field('POST') }}
            @else
            <form method="POST" action="{{route('locations.update', ['site'=>$site,'location' => $location])}}">
            {{ method_field('PUT') }}
            @endif
    @csrf
        <x-form>
            <x-form-section title="Location Details" columns="2">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" :value="__('Name')"/>

                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                  :value="old('name', $location->name)"
                                  required autofocus/>

                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="site_id" :value="__('Site')"/>

                    <select id="site_id" class="block mt-1 w-full" type="text" name="site_id" required>
                        <option selected="true" disabled>Please Select an Option...</option>
                        {{!! $otherSites !!}}
                    </select>

                    <x-input-error :messages="$errors->get('site_id')" class="mt-2"/>
                </div>
                <div class="mt-4" for="disabled" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="disabled" name="disabled"
                            @if ($location->disabled) checked @endif />
                    <div style="width: 1em;" @if ($location->id == Auth::id()) disabled @endif></div>
                    <x-input-label for="disabled" value="User Disabled"/>
                </div>
                <x-form-section title="Other Locations" columns="2">
                <x-input-label/>
                <x-transfer-list>
                    @foreach($otherLocations as $oloc)
                        <x-form-section columns="2">
                            <div class="mt-4" for="locs[{{$oloc->id }}]"
                                    style="display: flex; padding-bottom: 1em;">
                                <input type="checkbox" id="rules[{{$oloc->id }}]" name="rules[{{$oloc->id }}]"
                                        @if (array_key_exists($oloc->id,$rules) && $rules[$oloc->id]) checked @endif />
                                <div style="width: 1em;"></div>
                                <x-input-label for="rules[{{$oloc->id }}]" :value="__($oloc->name)"/>
                            </div>
                        </x-form-section>
                    @endforeach
                </x-transfer-list>
            </x-form-section>
            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create location' : 'Update Location' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    </x-slot>
    </x-form>
</form>
</div>
</x-app-layout>
