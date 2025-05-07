@if($user_agent->isDesktop() || $showOnMobile === true )
    <td align={{$align}} class="border-b dark:border-slate-600 font-semibold p-4 lg:pl-8 text-slate-600 text-left">
        {{$slot}}
    </td>
@endif
