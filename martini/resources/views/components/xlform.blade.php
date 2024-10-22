<div class="grid grid-cols-1 md:grid-cols-10 gap-4 mx-auto">
    <div class="md:col-span-9 sm:grid-cols-1">
            {{$slot}}
    </div>
    <div class="col-span-1">
        <x-form-buttons>
            {{$buttons}}
        </x-form-buttons>
    </div>
</div>
