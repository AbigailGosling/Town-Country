<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
                Create New User
            @else
                {{ $location->name }}
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
            <form method="POST" action="{{route('locations.store')}}">
            {{ method_field('POST') }}
            @else
            <form method="POST" action="{{route('locations.update', ['location' => $location->id])}}">
            {{ method_field('PUT') }}
            @endif
    @csrf
        <x-form>
            <x-form-section title="Personal Information" columns="2">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" :value="__('Name')"/>

                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                  :value="old('name', $location->name)"
                                  required autofocus/>

                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>

                <div class="mt-4" for="disabled" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="disabled" name="disabled"
                            @if ($location->disabled) checked @endif />
                    <div style="width: 1em;" @if ($location->id == Auth::id()) disabled @endif></div>
                    <x-input-label for="disabled" value="User Disabled"/>
                </div>

            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create location' : 'Update Location' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    </x-slot>
    </x-form>
</form>


    @if ($isNew)
        <form method="POST" action="{{ route('locations.store') }}">
            {{ method_field('POST') }}
        </form>
            @else
                <form method="POST" action="{{ route('locations.update', ['location' => $location]) }}">
                    {{ method_field('PUT') }}
                    @endif
                    @csrf

                </form>
    </div>
</x-app-layout>
