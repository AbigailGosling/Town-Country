<a href="{{$route ? route($route, $params) : ''}}">
<div class="flex grid grid-cols-6 bg-white hover:bg-slate-200 shadow-md cursor-pointer mb-2 h-20 rounded-md" onclick="{{$submit ? '$(this).closest(`form`).submit()' : ''}}">
    @if($background === 'green')
    <div class="bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center rounded-l-md">
    @elseif($background === 'orange')
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 flex items-center justify-center rounded-l-md">
    @endif
    <div class="m-auto" style="vertical-align: center !important">
        <span>
    <i class="fa {{$iconClass}} fa-lg text-white"></i>
        </span>
    </div>
    </div>
    <div class="p-4 col-span-5 my-auto font-semibold subpixel-antialiased">
    {{$title}}
    </div>
</div>
</a>
