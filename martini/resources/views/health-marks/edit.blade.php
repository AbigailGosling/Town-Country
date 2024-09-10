<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
                Create Health Mark
            @else
                Edit Health Mark
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
    <form method="POST" action="{{route('health_marks.store')}}">
    {{ method_field('POST') }}
    @else
    <form method="POST" action="{{route('health_marks.update', array('health_mark'=>$health_mark))}}">
    {{ method_field('PUT') }}
    @endif
    @csrf
        <x-form>
            <x-form-section title="Health Mark" columns="2">

                <div>
                    <x-input-label for="name" :value="__('Name')"/>

                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                  :value="old('name', $health_mark->name)" required/>

                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>

                <div class="mt-4" for="disabled" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="disabled" name="disabled"
                            @if ($health_mark->disabled) checked @endif />
                    <x-input-label for="disabled" value="Health Mark Disabled"/>
                </div>

            </x-form-section>

            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create Health Mark' : 'Update Health Mark' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    </x-slot>
    </x-form>
    </div>
</x-app-layout>
