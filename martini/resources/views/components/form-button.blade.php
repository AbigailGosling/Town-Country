<?php if($route):?>
<a href="{{$route ? route($route, $params) : ''}}">
<?php endif; ?>
<?php
    $onclick = '';
    if ($submit) $onclick = '$(this).closest(`form`).submit()';
    else if ($title == "Export" || $title == "Loading...") $onclick = 'ExportData()';

    $disabled = '';
    if ($disable) $disabled = "pointer-events:none;";

    if ($id !="") $id2=$id."-text";
    else $id2="";
?>
    <div id="{{$id}}" class="flex grid grid-cols-6 bg-white hover:bg-slate-200 shadow-md cursor-pointer mb-2 h-20 rounded-md" style="{{$disabled}}" onclick="{{$onclick}}">
    @if($background === 'green')
    <div class="bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center rounded-l-md">
    @elseif($background === 'orange')
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 flex items-center justify-center rounded-l-md">
    @elseif($background === 'red')
    <div class="bg-gradient-to-r from-red-500 to-red-600 flex items-center justify-center rounded-l-md">
    @endif
    <div class="m-auto" style="vertical-align: center !important">
        <span>
    <i class="fa {{$iconClass}} fa-lg text-white"></i>
        </span>
    </div>
    </div>
    <div id="{{$id2}}" class="p-4 col-span-5 my-auto font-semibold subpixel-antialiased">
    {{$title}}
    </div>
</div>
<?php if ($route):?>
</a>
<?php endif;?>
