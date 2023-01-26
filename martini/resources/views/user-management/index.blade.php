<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>
    <form  method="get" action="{{route('users.search')}}">
            <x-search search_term="{{$search_term ? $search_term : ''}}">

            </x-search>
    </form>

            <x-data-table>
                <x-slot:headers>
                    <th class="border-b dark:border-slate-600 font-semibold p-4 pl-8 pt-0 text-slate-900 text-left">Full Name</th>
                    <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-left">Email Address</th>
                    <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-center">No Permissions</th>
                    <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-center">Created At</th>
                    <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-center">Updated At</th>
                    <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-left">Action</th>
                </x-slot:headers>
                <slot>
                    @foreach($users as $user)
                        <tr>
                            <td class="border-b dark:border-slate-600 font-semibold p-4 pl-8 text-slate-600 text-left">{{$user->name}}</td>
                            <td class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-left">{{$user->email}}</td>
                            <td class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-center">{{$user->permissions_count}}</td>
                            <td class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-center">{{$user->created_at->format('d/m/Y')}}</td>
                            <th class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-center">{{$user->updated_at->format('d/m/Y')}}</th>
                            <td class="border-b dark:border-slate-600 p-2 pr-8">
                                <div class="grid-cols-2">
                                    <div class="grid grid-cols-1">
                                        <x-table-action-button route="users.edit" :id="$user->id"></x-table-action-button>
{{--                                        <x-table-action-button route="users.edit" :id="$user->id" type="delete"></x-table-action-button>--}}
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
