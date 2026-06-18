@if($user_agent->isDesktop() || $showOnMobile === true )
<th style="width:{{$width}}" class="border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 pt-0 text-slate-900 text-center">
    {{$slot}}
</th>
@endif
