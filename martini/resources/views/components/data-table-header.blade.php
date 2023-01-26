@if($user_agent->isDesktop() || $showOnMobile === true )
<th class="border-b dark:border-slate-600 font-semibold p-4 pl-8 pt-0 text-slate-900 text-left">
    {{$slot}}
</th>
@endif
