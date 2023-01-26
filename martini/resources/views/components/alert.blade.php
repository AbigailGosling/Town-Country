@if($type === 'success')
<div class="bg-green-500 mt-5 p-6 text-white-50 sm:rounded-md">
    <div class="flex flex-row gap-4">
        <div class="text-green-100">
            <i class="fa fa-check-circle"></i>
            <div class="h-max border-1"></div>
        </div>
        <div class="text-green-100 font-semibold">
            {{$slot}}
        </div>
    </div>
</div>
@endif

@if($type === 'error')
    <div class="bg-red-500 mt-5 p-6 text-white-50 sm:rounded-md">
        <div class="flex flex-row gap-4">
            <div class="text-red-100">
                <i class="fa fa-circle-xmark"></i>
                <div class="h-max border-1"></div>
            </div>
            <div class="text-red-100 font-semibold">
                {{$slot}}
            </div>
        </div>
    </div>
@endif


