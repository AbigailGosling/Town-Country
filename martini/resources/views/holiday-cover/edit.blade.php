<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
                Create Cover
            @else
                Edit Cover
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
    @if ($isNew)
            <form method="POST" action="{{route('holidays.store')}}">
            {{ method_field('POST') }}
            @else
            <form method="POST" action="{{route('holidays.update', ['holiday' => $hc->id] ) }}">
            {{ method_field('PUT') }}        
            @endif
    @csrf
        <x-form>
            <x-form-section title="Personal Information" columns="2">
                <!-- Name Field -->
                <div>
                    <x-input-label for="absentee" :value="__('Who Is Absent?')"/>
                    <select id="absentee" class="block mt-1 w-full" type="text" name="absentee" required>
                        <option selected="true" disabled>Please Select an Option...</option>
                        @foreach ($users as $user)
                        @if (isset($hc) && $hc->absentUser()->id == $user->id)
                        <option selected="true" value={{ $user->id }}>{{ $user->name }}</option>
                        @else
                        <option value={{ $user->id }}>{{ $user->name }}</option>
                        @endif
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('absentee')" class="mt-2"/>
                </div>
                <!-- Email Field -->
                <div>
                    <x-input-label for="cover" :value="__('Who Is Covering?')"/>

                    <select id="cover" class="block mt-1 w-full" type="text" name="cover" required>
                        <option selected="true" disabled>Please Select an Option...</option>
                        @foreach ($users as $user)
                        @if (isset($hc) && $hc->coverUser()->id == $user->id)
                        <option selected="true" value={{ $user->id }}>{{ $user->name }}</option>
                        @else
                        <option value={{ $user->id }}>{{ $user->name }}</option>
                        @endif
                        @endforeach
                    </select>

                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>

            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create Cover' : 'Update Cover' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    </x-slot>
    </x-form>
</form>


    @if ($isNew)
        <form method="POST" action="{{ route('users.store') }}">
            {{ method_field('POST') }}
        </form>
            @else
                <form method="POST" action="{{ route('users.update', ['user' => $user]) }}">
                    {{ method_field('PUT') }}
                    @endif
                    @csrf

                </form>
    </div>
</x-app-layout>
