<div class="grid grid-cols-1 md:grid-cols-4 gap-4 max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
    <div class="md:col-span-3 sm:grid-cols-1">
            {{$slot}}
    </div>
    <div class="col-span-1">
        <x-form-buttons>
            {{$buttons}}
        </x-form-buttons>
    </div>
</div>
