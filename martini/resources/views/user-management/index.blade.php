<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>


            <div class="grid grid-cols-3">
                <div class="col-span-2">
                </div>
                <div>
                    <form class="flex items-center pb-4" method="get" action="{{route('users.search')}}">
                        <label for="simple-search" class="sr-only">Search</label>
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path></svg>
                            </div>
                            <input type="text" name="search" value="{{$search_term ? $search_term : ''}}" id="simple-search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Search" required>
                        </div>
                        <button type="submit" class="p-2.5 ml-2 text-sm font-medium text-white bg-green-500 rounded-lg border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <span class="sr-only">Search</span>
                        </button>
                    </form>
                </div>
            </div>
            <div class="bg-gray-200 overflow-hidden shadow-sm sm:rounded-lg ml-6 mr-6">
                <table class="border-collapse table-auto w-full text-sm mt-4">
                    <thead class="bg-gray-200">
                    <tr>
                        <th class="border-b dark:border-slate-600 font-semibold p-4 pl-8 pt-0 text-slate-900 text-left">Full Name</th>
                        <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-left">Email Address</th>
                        <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-center">No Permissions</th>
                        <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-center">Created At</th>
                        <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-center">Updated At</th>
                        <th class="border-b dark:border-slate-600 font-semibold p-4 pt-0 pb-3 text-slate-900 text-left">Action</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white">
                    @foreach($users as $user)
                    <tr>
                        <td class="border-b dark:border-slate-600 font-semibold p-4 pl-8 text-slate-600 text-left">{{$user->name}}</td>
                        <td class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-left">{{$user->email}}</td>
                        <td class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-center">{{$user->permissions_count}}</td>
                        <td class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-center">{{$user->created_at->format('d/m/Y')}}</td>
                        <th class="border-b dark:border-slate-600 font-semibold p-4 text-slate-600 text-center">{{$user->updated_at->format('d/m/Y')}}</th>
                        <td class="border-b dark:border-slate-600 p-2 pr-8">
                            <div class="grid-cols-2">
                                <div class="grid grid-cols-2">
                                    <x-table-action-button route="users.edit" :id="$user->id"></x-table-action-button>
                                    <x-table-action-button route="users.edit" :id="$user->id" type="delete"></x-table-action-button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        <br>
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
