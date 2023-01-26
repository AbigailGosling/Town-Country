<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
                Create New User
            @else
                {{ $user->name }}
            @endif
        </h2>
    </x-slot>
    <div class="py-12">
<form method="POST" action="{{route('users.update', ['user' => $user->id])}}">
    {{ method_field('PUT') }}
    @csrf
        <x-form>
            <x-form-section title="Personal Information" columns="2">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" :value="__('Name')"/>

                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                  :value="old('name', $user->name)"
                                  required autofocus/>

                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>
                <!-- Email Field -->
                <div>
                    <x-input-label for="email" :value="__('Email')"/>

                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                  :value="old('email', $user->email)" required/>

                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>


                @can('admin', Auth::user())
                    <div class="mt-4" for="disabled" style="display: flex; padding-bottom: 1em;">
                        <input type="checkbox" id="disabled" name="disabled"
                               @if ($user->disabled) checked @endif />
                        <div style="width: 1em;" @if ($user->id == Auth::id()) disabled @endif></div>
                        <x-input-label for="disabled" value="User Disabled"/>
                    </div>
                @endcan

            </x-form-section>
            <x-form-section title="Permissions" columns="1">
                <x-input-label/>
                <x-transfer-list>
                    @foreach($permissions as $perm_category)
                        <x-transfer-list-section title="{{$perm_category->name}}">
                            <x-form-section columns="2">
                                @foreach ($perm_category->permissions as $perm)
                                    <div class="mt-4" for="perms[{{ $perm->name }}]"
                                         style="display: flex; padding-bottom: 1em;">
                                        <input type="checkbox" id="perms[{{ $perm->name }}]" name="perms[{{ $perm->name }}]"
                                               @if ($user->hasPermission($perm)) checked @endif />
                                        <div style="width: 1em;"></div>
                                        <x-input-label for="perms[{{ $perm->name }}]" :value="__($perm->label)"/>
                                    </div>
                                @endforeach
                            </x-form-section>

                        </x-transfer-list-section>
                    @endforeach
                </x-transfer-list>
            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    <x-form-button title="{{ $isNew ? 'Create User' : 'Update User' }}" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    <x-form-button title="{{ 'Forgotten Password' }}" iconClass="fa-key" background="orange"
                   route="{{ $isNew ? 'users.store' : 'users.update' }}" :params="$isNew ? '' : $user->id">
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
</x-app-layout>
