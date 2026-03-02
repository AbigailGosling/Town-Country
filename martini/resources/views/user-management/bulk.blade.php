<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>
    <form method="get" action="{{route('bulkpermission.search')}}">
        <x-search search_term="{{$search_term ? $search_term : ''}}">
        </x-search>
</form>
<div></div>
    <div></div>
    <form method="POST" action="{{ route('bulkpermission.save',['page'=>app('request')->input('page')])}}" enctype="multipart/form-data">
    @method("PUT")
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div><x-form-button title="{{ 'Save Changes' }}" iconClass="fa-pencil" background="green" :submit="true">
    </x-form-button></div>


    </div>
    <x-data-table>
        <x-slot:headers>
            <x-data-table-header>Username</x-data-table-header>
            <x-data-table-header>Disabled?</x-data-table-header>
            <x-data-table-header>Hidden?</x-data-table-header>
            @foreach ($permissions as $permission)
            <x-data-table-header>{{$permission->label}}</x-data-table-header>
            @endforeach
        </x-slot:headers>
        <slot>
            @foreach($users as $user)
                <tr>
                    <x-data-table-column>{{$user->name}}</x-data-table-column>
                    <x-data-table-column><input type="checkbox" id="disabled[{{ $user->id }}]" name="disabled[{{ $user->id }}]" @if ($user->disabled) checked @endif /></x-data-table-column>
                    <x-data-table-column><input type="checkbox" id="is_hidden[{{ $user->id }}]" name="is_hidden[{{ $user->id }}]" @if ($user->is_hidden) checked @endif /></x-data-table-column>
                    @foreach ($permissions as $permission)
                    <x-data-table-column><input type="checkbox" id="perms[{{ $user->id }}][{{ $permission->id }}]" name="perms[{{ $user->id }}][{{ $permission->id }}]"
                                               @if ($user->permissions->contains("id",$permission->id)) checked @endif /></x-data-table-column>
                    @endforeach
                </tr>
            @endforeach
        </slot>
    </x-data-table>
    <form>
    <br>
    {{ $users->links() }}
</x-app-layout>
