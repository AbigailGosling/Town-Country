<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
            {{ $origin->name. " : Create New Movement Rule" }}
            @else
                {{ $origin->name. " to ".$destination->name  }}
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
        <form method="POST" action="{{route('stockmovements.store')}}">
        {{ method_field('POST') }}
    @else
        <form method="POST" action="{{route('stockmovements.update', ['stockmovementrule'=>$stockmovementrule])}}">
        {{ method_field('PUT') }}
    @endif
    @csrf
        <x-form>
            <x-form-section title="Movement Details" columns="1">
                <!-- Name Field -->
                <div>
                    <x-input-label for="origin" :value="__('Origin')"/>

                    <select id="origin" class="block mt-1 w-full" type="text" name="origin" required>
                        <option selected="true" value="{{$origin->id}}">{{$origin->name}}</option>
                    </select>

                    <x-input-error :messages="$errors->get('origin')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="destination" :value="__('Destination')"/>

                    <select id="destination" class="block mt-1 w-full" type="text" name="destination" required>
                        <option selected="true" disabled>Please Select an Option...</option>
                        @foreach ($sites as $site)
                        @if ($site->id == $destination->id || $site->disabled == 0 || old('destination'))
                        <option @if($site->id == $destination->id)selected="true"@endif value="{{$site->id}}">{{$site->name}}</option>
                        @endif
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('destination')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="days" :value="__('Days of Lead time')"/>

                    <x-text-input id="days" class="block mt-1 w-full" type="text" name="days"
                                  :value="old('days', $stockmovementrule->days)"
                                  required/>

                    <x-input-error :messages="$errors->get('days')" class="mt-2"/>
                </div>
                <div class="mt-4" for="mirror" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="mirror" name="mirror" @if($stockmovementrule->isMirrored()) checked @endif/>
                    <div style="width: 1em;" disabled></div>
                    <x-input-label for="mirror" value="Mirror Direction"/>
                </div>
            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create Movement' : 'Update Movement' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    </x-slot>
    </x-form>
</form>
</div>
</x-app-layout>
