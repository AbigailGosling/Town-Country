<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Document Activity For: ').$inboundcontainer->internal_number }}
        </h2>
    </x-slot>

    <div class="py-12">
        <!-- Create Form -->
        <form method="POST" action="{{ route('inbound-approvals.store',$inboundcontainer) }}" enctype="multipart/form-data">
            @csrf

            <x-form>
                <x-form-section title="Approval Details" columns="1">
                    <!-- Approved -->
                    <div>
                        <x-input-label for="approved" :value="__('Approve Container?')" />
                        <select id="approved" name="approved" class="block mt-1 w-full">
                            @if ($inboundcontainer->admin_approved == false)
                            <option value="0" {{ old('approved') == 0 ? 'selected' : '' }}>
                                {{ __('No') }}
                            </option>
                            @endif
                            <option value="1" {{ old('approved') == 1 ? 'selected' : '' }}>
                                {{ __('Yes') }}
                            </option>
                        </select>
                        <x-input-error :messages="$errors->get('approved')" class="mt-2" />
                    </div>
                    <!-- File -->
                    <div>
                        <x-input-label for="file" :value="__('File')" />
                        <input id="file" class="block mt-1 w-full" type="file" name="file" />
                        <x-input-error :messages="$errors->get('file')" class="mt-2" />
                    </div>
                    <!-- Comments -->
                    <div>
                        <x-input-label for="comments" :value="__('Comments')" />
                        <x-text-input id="comments" class="block mt-1 w-full" type="text"
                            name="comments" value="{{ old('comments') }}" />
                        <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                    </div>
                </x-form-section>

                <!-- Action Button -->
                <x-slot name="buttons">
                    <x-form-button title="Save" background="green" iconClass="fa-save" :submit="true" />
                </x-slot>
            </x-form>
        </form>
        <div class="mt-10">
            <h3 class="text-lg font-semibold mb-4">{{ __('Documents') }}</h3>
                <x-data-table>
                    <x-slot:headers>
                        <x-data-table-header>User</x-data-table-header>
                        <x-data-table-header>Approved</x-data-table-header>
                        <x-data-table-header>File</x-data-table-header>
                        <x-data-table-header>Comments</x-data-table-header>
                        <x-data-table-header>Added At</x-data-table-header>
                    </x-slot:headers>
                    <slot>
                        @foreach ($approvals as $approval)
                        <tr>
                            <x-data-table-column>{{ $approval->user?->name ?? 'Unknown' }}</x-data-table-column>
                            <x-data-table-column>
                                @if($approval->approved)
                                    <span class="text-green-600 font-semibold">Yes</span>
                                @else
                                    <span class="text-red-600 font-semibold">No</span>
                                @endif
                            </x-data-table-column>
                            <x-data-table-column>
                                @if($approval->hasFile())
                                    <a href="{{ route('files.download', $approval->getFile()) }}" target="_blank" class="text-blue-600 underline">
                                       {{$approval->getFile()?->original_name}}
                                    </a>
                                @else
                                    —
                                @endif
                            </x-data-table-column>
                            <x-data-table-column>{{$approval->comments}}
                            </x-data-table-column>
                            <x-data-table-column>{{ $approval->created_at->format('Y-m-d H:i') }}
                                <a href="{{route('inbound-approvals.destroy',['approval'=>$approval,'container'=>$inboundcontainer])}}">
                                    <button class="rounded bg-red-500 hover:bg-red-700 w-6 h-6" href=""><i class="fas fa-trash text-red-100"></i></button>
                                </a>
                            </x-data-table-column>
                        </tr>
                        @endforeach
                    </slot>
                </x-data-table>
            </div>
        </div>
    </div>
</x-app-layout>
