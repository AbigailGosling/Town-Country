<?php

namespace App\View\Components;

use App\Models\PickerSheet;
use App\Models\PickWeightOut;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;

class DraggablePick extends Component
{
    public $pickWeightOut;
    public $pickerSheet;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(PickWeightOut $pickWeightOut)
    {
        Log::debug($pickWeightOut->id);
        Log::debug($pickWeightOut->pickersheet_id);
        $this->pickerSheet = PickerSheet::find($pickWeightOut->pickersheet_id);
        $this->pickWeightOut = $pickWeightOut;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.draggable-pick');
    }
}
