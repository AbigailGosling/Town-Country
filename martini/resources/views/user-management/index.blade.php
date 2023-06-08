<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div><x-form-button title="{{ 'Create User' }}" iconClass="fa-pencil" background="green"
                   route="users.create">
    </x-form-button></div>
    <div></div>
    <div></div>
    <form method="get" action="{{route('users.search')}}">
            <x-search search_term="{{$search_term ? $search_term : ''}}">
            </x-search>
    </form>
    </div>
    

            <x-data-table>
                <x-slot:headers>
                    <x-data-table-header :show-on-mobile="false">Full Name</x-data-table-header>
                    <x-data-table-header>Email Address</x-data-table-header>
                    <x-data-table-header :show-on-mobile="false">Created At</x-data-table-header>
                    <x-data-table-header :show-on-mobile="false">Updated At</x-data-table-header>
                    <x-data-table-header></x-data-table-header>
                </x-slot:headers>
                <slot>
                    @foreach($users as $user)
                        <tr>
                            <x-data-table-column :show-on-mobile="false">{{$user->name}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="true">{{$user->email}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="false">{{$user->created_at->format('d/m/Y')}}</x-data-table-column>
                            <x-data-table-column :show-on-mobile="false">{{$user->updated_at->format('d/m/Y')}}</x-data-table-column>
                            <td class="border-b dark:border-slate-600 p-2 pr-8">
                                <div class="grid-cols-2">
                                    <div class="grid grid-cols-1">
                                        <x-table-action-button route="users.edit" :id="$user->id"></x-table-action-button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </slot>
            </x-data-table>
        <br>
            {{ $users->links() }}
</x-app-layout>
