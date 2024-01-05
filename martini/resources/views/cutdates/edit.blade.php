<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
                Create New Cut Group Rule
            @else
                Edit Cut Group Rule
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
            <form method="POST" action="{{route('cutdates.store')}}">
            {{ method_field('POST') }}
            @else
            <form method="POST" action="{{route('cutdates.update', $cgnd->id) }}">
            {{ method_field('PUT') }}
            @endif
    @csrf
        <x-form>
            <x-form-section title="Cut Group Rule" columns="2">

                <div>
                    <x-input-label for="nationality_id" :value="__('Nationality')"/>

                    <select id="nationality_id" class="block mt-1 w-full" type="text" name="nationality_id" required>
                        <option selected="true" disabled>Please Select an Option...</option>
                        {!! $nationalities !!}
                    </select>
                    <x-input-error :messages="$errors->get('nationality_id')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="species_id" :value="__('Species')"/>

                    <select id="species_id" class="block mt-1 w-full" type="text" name="species_id" required>
                        <option selected="true" disabled>Please Select an Option...</option>
                        {!! $species !!}
                    </select>

                    <x-input-error :messages="$errors->get('species_id')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="cutgroup_id" :value="__('Cut Group')"/>

                    <select id="cutgroup_id" class="block mt-1 w-full" type="text" name="cutgroup_id" required>
                        <option selected="true" disabled>Please Select an Option...</option>
                        {!! $cutgroups !!}
                    </select>

                    <x-input-error :messages="$errors->get('cutgroup_id')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="warning" :value="__('Warning (In Days)')"/>

                    <x-text-input id="warning" class="block mt-1 w-full" type="text" name="warning"
                                  :value="old('warning', $cgnd->warning)"
                                  required autofocus/>

                    <x-input-error :messages="$errors->get('warning')" class="mt-2"/>
                </div>

                <div>
                    <x-input-label for="danger" :value="__('Danger (In Days)')"/>

                    <x-text-input id="danger" class="block mt-1 w-full" type="text" name="danger"
                                  :value="old('danger', $cgnd->danger)"
                                  required autofocus/>

                    <x-input-error :messages="$errors->get('danger')" class="mt-2"/>
                </div>
            </x-form-section>

            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create Rule' : 'Update Rule' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    </x-slot>
    </x-form>
    </div>
</x-app-layout>
@stack('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            console.log("test");
            $('#species_id').on('change', function () {
                console.log("test");
                var species = this.value;
                $("#cutgroup_id").html('<option selected="true" disabled>Please Wait...</option>');
                $.ajax({
                    url: "{{url('api/fetch-cutgroups')}}",
                    type: "GET",
                    data: {
                        species_id: species
                    },
                    dataType: 'text',
                    success: function (result) {
                        $('#cutgroup_id').html('<option selected="true" disabled>Please Select an Option...</option>'+result);
                    }
                });
            });
  
        });
    </script>