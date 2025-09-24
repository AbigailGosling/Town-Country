<x-app-layout>
    <x-slot name="header">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if ($isNew)
            {{ __('Add Product')}}
            @else
            {{ __('Edit Product: ') . $containerProduct->getProduct()?->getCut()->getSpecies()->name . __(' : ') . $containerProduct->getProduct()?->getCut()->name }}
            @endif

        </h2>
    </x-slot>

    <div class="py-12">

        @if ($isNew == false)
            <form method="POST" action="{{ route('container-product.update', ['container'=>$containerProduct->getContainer(),'containerProduct'=>$containerProduct]) }}">
            @method("PUT")
        @else
            <form method="POST" action="{{ route('container-product.store',$container) }}">
        @endif
            @csrf
            <x-form>
                <x-form-section columns="2">
                    <!-- Nationality -->
                    <div>
                        <x-input-label for="nationality" :value="__('Nationality')" />
                        <select id="nationality" class="block mt-1 w-full" name="nationality" required>
                            <option disabled="disabled" selected value="">Select Nationality</option>
                            @foreach ($nationalities as $nationality)
                            <option {{($nationality->id==old('nationality', $containerProduct->getProduct()?->nationality_id)) ? "selected":"";}} value="{{$nationality->id}}">{{$nationality->name}}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
                        </div>
                    <!-- Brand -->
                    <div>
                        <x-input-label for="brand" :value="__('Brand')" />
                        <select id="brand" class="block mt-1 w-full" name="brand" required>
                            <option disabled="disabled" selected value="">Select Brand</option>
                            @foreach ($brands as $brand)
                            <option {{($brand->id==old('brand', $containerProduct->getProduct()?->getBrand()?->id)) ? "selected":"";}} value="{{$brand->id}}">{{$brand->name}}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                        </div>

                    <!-- Species -->
                    <div>
                        <x-input-label for="species" :value="__('Species')" />
                        <select id="species" class="block mt-1 w-full" name="species" required>
                            <option disabled="disabled" selected value="">Select Species</option>
                            @foreach ($species as $specie)
                            <option {{($specie->id==old('species', $containerProduct->getProduct()?->getCut()?->getSpecies()?->id)) ? "selected":"";}} value="{{$specie->id}}">{{$specie->name}}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('species')" class="mt-2" />
                    </div>

                    <!-- CutGroup -->
                    <div>
                        <x-input-label for="cutgroup" :value="__('Cut')" />
                        <select id="cutgroup" class="block mt-1 w-full" name="cutgroup" required>
                            <option disabled="disabled" selected value="">Select Cut</option>
                        </select>
                        <x-input-error :messages="$errors->get('cutgroup')" class="mt-2" />
                    </div>

                    <!-- Cut -->
                    <div>
                        <x-input-label for="cut" :value="__('')" />
                        <select id="cut" class="block mt-1 w-full" name="cut" required>
                            <option disabled="disabled" selected value=""></option>
                        </select>
                        <x-input-error :messages="$errors->get('cut')" class="mt-2" />
                    </div>

                    <!-- Unit -->
                    <div>
                        <x-input-label for="unit" :value="__('Unit')" />
                        <select id="unit" class="block mt-1 w-full" name="unit" required>
                            <option disabled="disabled" selected value="">Select Unit</option>
                            <option {{("PPC"==old('unit', $containerProduct->getProduct()?->unit)) ? "selected":"";}} value="PPC">PPC</option>
                            <option {{("P"==old('unit', $containerProduct->getProduct()?->unit)) ? "selected":"";}} value="PPC">G/T</option>
                            <option {{("C"==old('unit', $containerProduct->getProduct()?->unit)) ? "selected":"";}} value="PPC">Cases</option>
                        </select>
                        <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                    </div>

                    <!-- Quantity -->
                    <div>
                        <x-input-label for="qty" :value="__('Quantity')" />
                        <x-text-input id="qty" class="block mt-1 w-full" type="number" step="1"
                            name="qty" value="{{ old('qty', $containerProduct->getProduct()?->quantity) }}" required />
                        <x-input-error :messages="$errors->get('qty')" class="mt-2" />
                    </div>

                    <!-- akg -->
                    <div>
                        <x-input-label for="akg" :value="__('Average Weight')" />
                        <x-text-input id="akg" class="block mt-1 w-full" type="number" step="0.001"
                            name="akg" value="{{ old('akg', $containerProduct->getProduct()?->akg) }}" required />
                        <x-input-error :messages="$errors->get('akg')" class="mt-2" />
                    </div>

                    <!-- rrp -->
                    <div>
                        <x-input-label for="rrp" :value="__('RRP')" />
                        <x-text-input id="rrp" class="block mt-1 w-full" type="number" step="0.001"
                            name="rrp" value="{{ old('rrp', $containerProduct->getProduct()?->price) }}" required />
                        <x-input-error :messages="$errors->get('akg')" class="mt-2" />
                    </div>
                </x-form-section>

                <!-- Action Buttons -->
                <x-slot name="buttons">
                    @if ($isNew == true)
                    <x-form-button id="save" title="Add Product" background="green" iconClass="fa-save" :submit="true" />
                    @else
                    <x-form-button id="save" title="Update Product" background="green" iconClass="fa-save" :submit="true" />
                    @endif

                </x-slot>
            </x-form>
        </form>
    </div>
</x-app-layout>
@push('stylesheets')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@stack("scripts")
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#species').on('change', loadCutGroups);
        $('#cutgroup').on('change', loadCuts);
        if (1 == <?php echo ($containerProduct->exists)?"1":"0"; ?>)
        {
            loadCutGroups();
        }
    });
    function loadCutGroups() {
        let speciesId = $('#species').val();

            resetSelect($('#cutgroup'), 'Select Cut');
            resetSelect($('#cut'), '');

            if (!speciesId) return;

            $.ajax({
                url: '/cutgroups/' + speciesId,
                type: 'GET',
                dataType: 'json',
                success: function(cuts) {
                    if (cuts.length) {
                        $.each(cuts, function(index, cut) {
                            if (cut.id == {{$containerProduct->getProduct()?->getCut()->cutgroup_id ?? "0"}}){
                                $('#cutgroup').append( $('<option>', { value: cut.id, text: cut.name, selected:true }));
                                setTimeout(() => {
                                    loadCuts();
                                }, 1);
                            }
                            else{
                                $('#cutgroup').append( $('<option>',  { value: cut.id, text: cut.name }));
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                }
            });
    }
    function loadCuts() {
        let cutGroupId = $('#cutgroup').val();

        resetSelect($('#cut'), 'Select Specific Cut');

        if (!cutGroupId) return;

        $.ajax({
            url: '/cuts/' + cutGroupId,
            type: 'GET',
            dataType: 'json',
            success: function(cuts) {
                if (cuts.length) {
                    $.each(cuts, function(index, cut) {
                        if (cut.id == {{$containerProduct->getProduct()?->cut_id?? "0"}}){
                            $('#cut').append( $('<option>', { value: cut.id, text: cut.name, selected:true }));
                        }
                        else{
                            $('#cut').append( $('<option>',  { value: cut.id, text: cut.name }));
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }
    function resetSelect($select, placeholder) {
        $select.empty().append(
            $('<option>', {
                value: '',
                text: placeholder,
                disabled: true,
                selected: true
            })
        );
    }
</script>
