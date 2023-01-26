<div class="grid grid-cols-4 gap-4 max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="col-span-3">
            {{$slot}}
    </div>
    <div class="col-span-1">
        <x-form-buttons>
            {{$buttons}}
        </x-form-buttons>
    </div>
</div>
