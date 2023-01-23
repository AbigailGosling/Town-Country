<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            @foreach ($users as $user)
            <a style="height: 100%; display:block;" href="{{route('users.show',$user)}}">
                <div class="p-6 bg-white border-b border-gray-200" :href="route('users.index')" :active="request()->routeIs('users')">
                    {{ $user->name }}    
                </div>
            </a>
            @endforeach
            </div>
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
