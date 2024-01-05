<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Cut Group Date Range Management') }}
        </h2>
    </x-slot>
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div><x-form-button title="{{ 'Create Date Range Rule' }}" iconClass="fa-pencil" background="green"
                   route="cutdates.create">
    </x-form-button></div>
    <div></div>
    <div></div>
    </div>
    <form method="POST" name="search" id="search" action="{{route('cutdates.search')}}">
        @csrf
    <div class="grid grid-cols-3 md:grid-cols-3 pl-4">
        <select id="species_id" class="block mt-1" type="text" name="species_id" required>
            <option selected="true" disabled>Search Species...</option>
            {!! $species !!}
        </select>
        <select id="cutgroup_id" class="block mt-1" type="text" name="cutgroup_id" required>
            @if ($showcutgroups)
            <option selected="true" disabled>Search Cut Group...</option>
            {!! $cutgroups !!}
            @else
            <option selected="true" disabled>...</option>
            @endif
        </select>
        <select id="nationality_id" class="block mt-1" type="text" name="nationality_id" required>
            <option selected="true" disabled>Search Nationality...</option>
            {!! $nationalities !!}
        </select>
        </div>
</form>
        </div>
            <x-data-table>
                <x-slot:headers>
                    <x-data-table-header :show-on-mobile="false">Species</x-data-table-header>
                    <x-data-table-header>Cut Group</x-data-table-header>
                    <x-data-table-header>Natinonality</x-data-table-header>
                    <x-data-table-header>Warning</x-data-table-header>
                    <x-data-table-header>Danger</x-data-table-header>
                    <th></th>
                </x-slot:headers>
                <slot>
                    @foreach($cutgroup_nationality_datess as $cutgroup_nationality_dates)
                        <tr>
                            <x-data-table-column :show-on-mobile="false">{{$cutgroup_nationality_dates->cutgroup->species()->first()->name}}</x-data-table-column>
                            <x-data-table-column>{{$cutgroup_nationality_dates->cutgroup->name}}</x-data-table-column>
                            <x-data-table-column>{{$cutgroup_nationality_dates->nationality->name}}</x-data-table-column>
                            <x-data-table-column>{{$cutgroup_nationality_dates->warning}}</x-data-table-column>
                            <x-data-table-column>{{$cutgroup_nationality_dates->danger}}</x-data-table-column>
                            <td class="border-b dark:border-slate-600">
                            <x-table-action-button route="cutdates.edit" :id="$cutgroup_nationality_dates->id"></x-table-action-button>
                            </td>
                        </tr>
                    @endforeach
                </slot>
            </x-data-table>
        <br>
            {{ $cutgroup_nationality_datess->links() }}
</x-app-layout>
@stack('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#species_id').on('change', function () {
                $("#search").submit();
            });
            $('#nationality_id').on('change', function () {
                $("#search").submit();
            });
            $('#cutgroup_id').on('change', function () {
                $("#search").submit();
            });
  
        });
    </script>