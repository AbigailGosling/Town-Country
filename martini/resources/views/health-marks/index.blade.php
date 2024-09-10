<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Health Mark Management') }}
        </h2>
    </x-slot>
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div><x-form-button title="{{ 'Create Health Mark' }}" iconClass="fa-pencil" background="green"
                   route="health_marks.create">
    </x-form-button></div>
    <div></div>
    <div></div>
    </div>
    <form method="get" action="{{route('health_marks.search')}}">
        <x-search search_term="{{$search_term ? $search_term : ''}}">
        </x-search>
    </form>
        </div>
            <x-data-table>
                <x-slot:headers>
                    <x-data-table-header>Name</x-data-table-header>
                    <x-data-table-header></x-data-table-header>
                    <x-data-table-header></x-data-table-header>
                    <x-data-table-header></x-data-table-header>
                    <x-data-table-header></x-data-table-header>

                </x-slot:headers>
                <slot>
                    @foreach($health_marks as $health_mark)
                        <tr>
                            <x-data-table-column>{{$health_mark->name}}</x-data-table-column>
                            <x-data-table-header></x-data-table-header>
                            <x-data-table-header></x-data-table-header>
                            <x-data-table-header></x-data-table-header>
                            <x-data-table-column><x-table-action-button route="health_marks.edit" :id="$health_mark->id"></x-table-action-button></x-data-table-column>

                        </tr>
                    @endforeach
                </slot>
            </x-data-table>
        <br>
            {{ $health_marks->links() }}
</x-app-layout>
