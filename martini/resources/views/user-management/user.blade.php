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
    @if ($isNew)
            <form method="POST" action="{{route('users.store')}}">
            {{ method_field('POST') }}
            @else
            <form method="POST" action="{{route('users.update', ['user' => $user->id])}}">
            {{ method_field('PUT') }}
            @endif
    @csrf
        <x-form>
            <x-form-section title="Personal Information" columns="1">
                <!-- Name Field -->
                <div>
                    <x-input-label for="name" :value="__('Name')"/>

                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                  :value="old('name', $user->name)"
                                  required autofocus/>

                    <x-input-error :messages="$errors->get('name')" class="mt-2"/>
                </div>
                <!-- Login Email Field -->
                <div>
                    <x-input-label for="email" :value="__('Login Email')"/>

                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                  :value="old('email', $user->email)" required/>

                    <x-input-error :messages="$errors->get('email')" class="mt-2"/>
                </div>
                <!-- Notification Email Field -->
                <div>
                    <x-input-label for="actual_email" :value="__('Notification Email')"/>

                    <x-text-input id="actual_email" class="block mt-1 w-full" type="email" name="actual_email"
                                :value="old('actual_email', $user->actual_email)" required/>

                    <x-input-error :messages="$errors->get('actual_email')" class="mt-2"/>
                </div>
                <div>
                    <x-input-label for="location_id" :value="__('Location')" />

                    <select id="location_id" name="location_id" class="block mt-1 w-full">
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @if ($user->location_id == $location->id) selected @endif>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-4" for="receive_short_stock" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="receive_short_stock" name="receive_short_stock"
                    @if ($user->receive_short_stock) checked @endif />
                    <div style="width: 1em;"></div>
                    <x-input-label for="receive_short_stock" :value="__('Receive Short Stock Notifications')"/>
                </div>
                <div class="mt-4" for="override_saledate_check" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="override_saledate_check" name="override_saledate_check"
                    @if ($user->override_saledate_check) checked @endif />
                    <div style="width: 1em;"></div>
                    <x-input-label for="override_saledate_check" :value="__('Override Next Day and Reservation Control')"/>
                </div>
                <div class="mt-4" for="hidden" style="display: flex; padding-bottom: 1em;">
                    <input type="checkbox" id="hidden" name="hidden"
                           @if ($user->is_hidden) checked @endif />
                    <div style="width: 1em;"></div>
                    <x-input-label for="hidden" :value="__('User Hidden')"/>
                </div>
                @can('admin', Auth::user())
                    <div class="mt-4" for="disabled" style="display: flex; padding-bottom: 1em;">
                        <input type="checkbox" id="disabled" name="disabled"
                               @if ($user->disabled) checked @endif />
                        <div style="width: 1em;" @if ($user->id == Auth::id()) disabled @endif></div>
                        <x-input-label for="disabled" :value="__('User Disabled')"/>
                    </div>
                    <div class="mt-4" for="use_two_factor" style="display: flex; padding-bottom: 1em;">
                        <input type="checkbox" id="use_two_factor" name="use_two_factor"
                               @if ($user->use_two_factor) checked @endif />
                        <div style="width: 1em;" @if ($user->id == Auth::id()) disabled @endif></div>
                        <x-input-label for="use_two_factor" :value="__('Use Two Factor Authentication')"/>
                    </div>
                @endcan
                </x-form-section>
            @can('admin',Auth::user())
                <x-form-section title="Sales Target" columns="1">
                        <x-text-input id="sale_target" type="number" step="0.01" name="sale_target" :value="old('sale_target', $user->sale_target)" />
                </x-form-section>
            @endcan
            @if(Auth::user()->id == $user->id || Auth::user()->can('admin'))
            <x-form-section title="Change Password" columns="1">
            @if(Auth::user()->id == $user->id)
                <x-input-label for="current-password" class="block mt-1 w-full" :value="__('Current Password')"/>
                <x-text-input id="password" type="password" name="password"></x-text-input>
            @endif
                <x-input-label for="new-password"  :value="__('New Password')"/>
                <x-text-input id="new-password" type="password" name="new_password"></x-text-input>
                <x-input-label for="confirm-password" class="block mt-1 w-full" :value="__('Confirm Password')"/>
                <x-text-input id="confirm-password" type="password" name="confirm_password">
                </x-text-input>
            </x-form-section>
            @endif
            <x-form-section title="Permissions" columns="1">
                <x-input-label/>
                <x-transfer-list>
                    @foreach($permissions as $perm_category)
                        <x-transfer-list-section title="{{$perm_category->name}}">
                            <x-form-section columns="1">
                                @foreach ($perm_category->permissions as $perm)
                                    <div class="mt-4" for="perms[{{ $perm->name }}]"
                                         style="display: flex; padding-bottom: 1em;@if (!$loggedInUser->can('admin') && !$loggedInUser->hasPermission($perm) && $loggedInUser->id != 54) display: none; @endif">
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
    @if (!$isNew)
    <x-form-button title="{{ 'Forgotten Password' }}" iconClass="fa-key" background="orange"
                   route="users.forgot-password" :params="$user->id">
    </x-form-button>
    @endif
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
