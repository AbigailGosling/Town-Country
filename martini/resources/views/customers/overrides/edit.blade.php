<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $customer->name." Overrides" }}
        </h2>
    </x-slot>
    <div class="py-12">
    <form method="POST" action="{{route('overrides.update_credit', ['customer' => $customer->id])}}">
    {{ method_field('POST') }}
    @csrf
        <x-form>
            <x-form-section title="Credit Check" columns="2">
                <!-- Name Field -->
                <div>
                    <x-input-label for="credit_comment" :value="__('Reason')"/>

                    <x-text-input id="credit_comment" class="block mt-1 w-full" type="text" name="credit_comment" required autofocus/>

                    <x-input-error :messages="$errors->get('credit_comment')" class="mt-2"/>
                </div>
            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    @if ($customer->override == 0)
    <x-form-button title="Credit Override Currently Disabled" background="orange" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    @else
    <x-form-button title="Credit Override Currently Enabled" background="green" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    @endif
    </x-slot>
    </x-form>
</form>
<form method="POST" action="{{route('overrides.update_del', ['customer' => $customer->id])}}">
    {{ method_field('POST') }}
    @csrf
        <x-form>
            <x-form-section title="Delivery Days" columns="2">
                <!-- Name Field -->
                <div>
                    <x-input-label for="del_comment" :value="__('Reason')"/>

                    <x-text-input id="del_comment" class="block mt-1 w-full" type="text" name="del_comment" required autofocus/>

                    <x-input-error :messages="$errors->get('del_comment')" class="mt-2"/>
                </div>
            </x-form-section>
            <!-- Form Action Buttons -->
            <x-slot name="buttons">
    @if ($customer->delivery_day_override == 0)
    <x-form-button title="Delivery Day Override Currently Disabled" background="orange" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    @else
    <x-form-button title="Delivery Day Override Currently Enabled" background="green" iconClass="fa-circle-arrow-right" :submit="true">
    </x-form-button>
    @endif
    </x-slot>
    </x-form>
</form>
    </div>
</x-app-layout>
