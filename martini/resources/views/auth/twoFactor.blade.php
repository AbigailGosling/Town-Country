<x-guest-layout>
    @section('pageTitle', 'Two Factor Verification')
    <x-auth-card>
        <x-slot name="logo">
            <a href="">
                <x-application-logo class="w-40 h-20 fill-current text-black-500" />
            </a>
        </x-slot>

        @if(session()->has('message'))
            <div class="mb-4 text-sm text-green-600">
                {{ session()->get('message') }}
            </div>
        @endif

        <div class="mb-4 text-sm text-gray-600">
            {{ __('You have received an email which contains a two factor login code.') }}
            {{ __('If you have not received it, press') }}
            <a href="{{ route('verify.resend') }}" class="underline text-gray-600 hover:text-gray-900">{{ __('here') }}</a>.
        </div>

        <form method="POST" action="{{ route('verify.store') }}">
            @csrf

            <div>
                <x-input-label for="two_factor_secret" :value="__('Two Factor Code')" />

                <x-text-input id="two_factor_secret"
                              class="block mt-1 w-full"
                              type="text"
                              name="two_factor_secret"
                              required
                              autofocus
                              placeholder="{{ __('Two Factor Code') }}" />

                <x-input-error :messages="$errors->get('two_factor_secret')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-primary-button>
                    {{ __('Verify') }}
                </x-primary-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
