<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inbound Containers') }}
        </h2>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 pl-4">
        <div>
            <x-form-button title="{{ 'Create Container' }}" iconClass="fa-ship" background="green"
                route="containers.create">
            </x-form-button>
        </div>
        <div></div>
        <div></div>
        <form method="get" action="{{ route('containers.search') }}">
            <x-search search_term="{{ $search_term ?? '' }}"></x-search>
        </form>
    </div>

    <x-data-table>
        <x-slot:headers>
            <x-data-table-header>Internal #</x-data-table-header>
            <x-data-table-header>Origin Port</x-data-table-header>
            <x-data-table-header>ETA</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Admin Approved</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Arrived</x-data-table-header>
            <x-data-table-header :show-on-mobile="false">Created At</x-data-table-header>
            <x-data-table-header></x-data-table-header>
        </x-slot:headers>

        <slot>
            @foreach($containers as $container)
                <tr>
                    <x-data-table-column>{{ $container->internal_number }}</x-data-table-column>
                    <x-data-table-column>{{ $container->origin_port }}</x-data-table-column>
                    <x-data-table-column>{{ $container->eta->format('d/m/Y') }}</x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">
                        @if($container->admin_approved)
                            <span class="text-green-600 font-semibold">Yes</span>
                        @else
                            <span class="text-red-600 font-semibold">No</span>
                        @endif
                    </x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">
                        @if($container->arrived)
                            <span class="text-green-600 font-semibold">Yes</span>
                        @else
                            <span class="text-red-600 font-semibold">No</span>
                        @endif
                    </x-data-table-column>
                    <x-data-table-column :show-on-mobile="false">
                        {{ ($container->created_at)?$container->created_at->format('d/m/Y'):"" }}
                    </x-data-table-column>
                    <td class="border-b dark:border-slate-600 p-2 pr-8">
                        <div class="grid grid-cols-2 gap-2">
                            <x-table-action-button route="containers.clone-container" type="clone" :id="$container->id" />
                            <x-table-action-button route="containers.edit" :id="$container->id" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </slot>
    </x-data-table>

    <br>
    {{ $containers->links() }}
</x-app-layout>
