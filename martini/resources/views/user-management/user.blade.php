
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                        @if ($isNew)                            
                            <form method="POST" action="{{ route('users.store') }}">
                            {{ method_field('POST') }}
                        @else
                            <form method="POST" action="{{ route('users.update',['user'=>$user]) }}">
                            {{ method_field('PUT') }}
                        @endif
                        @csrf

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />

                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name',$user->name)" required autofocus />

                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />

                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email',$user->email)" required />

                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div>
                        </div>
                        @can('admin', Auth::user())
                            <div class="mt-4" for="disabled" style="display: flex; padding-bottom: 1em;">
                            <input type="checkbox" id="disabled" name="disabled" @if($user->disabled) checked @endif /><div style="width: 1em;" @if($user->id == Auth::id()) disabled @endif></div>
                            <x-input-label for="disabled" value="Disabled" />
                            </div>
                            <div>
                            </div>
                            <div class="mt-4">
                            </div>
                            <x-input-label :value="__('Roles')" />
                            @foreach($perms as $perm)
                            <div class="mt-4" for="perms[{{$perm->name}}]" style="display: flex; padding-bottom: 1em;">
                            <input type="checkbox" id="perms[{{$perm->name}}]" name="perms[{{$perm->name}}]" @if($user->hasPermission($perm)) checked @endif /><div style="width: 1em;"></div>
                            <x-input-label for="perms[{{$perm->name}}]" :value="__($perm->label)" />
                            </div>
                            @endforeach
                        
                        @endcan
                        <div class="mt-4">
                            <x-primary-button class="ml-4">
                            @if ($isNew)
                            {{ __('Create') }}
                            @else
                            {{ __('Update') }}
                            @endif                          
                            </x-primary-button>
                        </div>
                    </form>
                </div class="p-6 bg-white border-b border-gray-200">
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
