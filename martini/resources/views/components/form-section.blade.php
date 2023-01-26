<div>
    <span class="h-1 w-full w-1/3 font-semibold">
    {{$title}}
    </span>
    <div class="grid grid-cols-1 md:grid-cols-{{$columns}} p-4 gap-5">
        {{$slot}}
    </div>
</div>
